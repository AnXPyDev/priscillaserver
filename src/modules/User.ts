import { createIs } from "typia";
import ServerModule, { RequestValidator } from "../ServerModule";
import { Hash, Hmac, createHash, createHmac, randomBytes, randomUUID } from "crypto";
import { PrismaClientKnownRequestError } from "@prisma/client/runtime/library";

interface Auth {
    user_id: number;
}

interface AuthSession {
    auth?: Auth;
}

namespace Validators {
    export const notLoggedIn: RequestValidator = (request, callback) => {
        const session = request.session as AuthSession;
        if (session.auth) {
            return callback.reject(1, "You are logged in");
        }
    };

    export const loggedIn: RequestValidator = (request, callback) => {
        const session = request.session as AuthSession;
        if (session.auth) {
            return callback.accept({ auth: session.auth });
        }
        return callback.reject(1, "You are not logged in");
    }
}

export default class UserModule extends ServerModule {
    constructor() {
        super("user");
    }

    randomString(length: number) {
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        const bytes = randomBytes(length);
        let result = "";
        for (const byte of bytes) {
            result += chars[byte % chars.length];
        }
        return result;
    }

    hashPassword(password: string, salt: string) {
        return createHmac("sha256", salt).update(password).digest("base64");
    }

    override init() {
        interface WatchConfig {
            room_id: number
        }

        const watchTokens = new Map<string, WatchConfig>();

        const watch_socks = this.server.socks.of("/watch");
        const db = this.server.db;
        const log = this.server.instance.log;
        const events = this.server.events;

        watch_socks.on("connection", async (socket) => {
            const auth: { token?: string } = socket.handshake.auth;

            if (!auth.token) {
                socket.send("No token provided");
                return socket.disconnect();
            }

            const conf = watchTokens.get(auth.token);

            if (!conf) {
                socket.send("Invalid token");
                return socket.disconnect()
            }

            const signal = `ROOM:${conf.room_id}`;

            const listener = async (name: string, ...args: any[]) => {
                socket.emit("event", name, ...args);
            }

            events.on(signal, listener);

            socket.on("disconnect", () => {
                events.removeListener(signal, listener);
            });
        });

        this.handle("register", async (data, callback) => {
            interface Params {
                username?: string,
                password?: string
            }

            const params = data.body as Params;

            if (!params.username) {
                return callback.reject(1, "No username provided");
            }

            if (!params.password) {
                return callback.reject(1, "No password provided");
            }

            if (params.username.length < 4) {
                return callback.reject(2, "Username must be at least 4 characters long");
            }

            if (params.password.length < 8) {
                return callback.reject(2, "Password must be at least 8 characters long");
            }

            {
                const user = await db.user.findFirst({
                    where: {
                        name: params.username
                    }
                });
                if (user) {
                    return callback.reject(3, "User already exists");
                }
            }

            const salt = this.randomString(12);
            const hash = this.hashPassword(params.password, salt);

            const user = await db.user.create({
                data: {
                    name: params.username,
                    password_hash: hash,
                    password_salt: salt
                }
            });

            return callback.success({ message: `User ${user.name} created successfully`});
        }, { validators: [ Validators.notLoggedIn ]});

        this.handle("login", async (data, callback, io) => {
            interface Params {
                username?: string;
                password?: string;
            }

            const params = data.body as Params; 

            if (!params.username) {
                return callback.reject(1, "No username provided");
            }

            if (!params.password) {
                return callback.reject(1, "No password provided");
            }

            const user = await db.user.findFirst({
                where: {
                    name: params.username
                }
            });
            if (!user) {
                return callback.reject(2, "No such user");
            }

            const hash = this.hashPassword(params.password, user.password_salt);
            if (hash != user.password_hash) {
                return callback.reject(2, `Wrong password`);
            }

            const session = io.request.session as any as AuthSession;
            session.auth = {
                user_id: user.id
            };
            io.request.session.save();

            return callback.success({ message: `Logged in as ${user.name}`});
        }, { validators: [ Validators.notLoggedIn ] });

        this.handle("info", async (data, callback) => {
            const auth: Auth = data.auth;

            const user = await db.user.findFirst({
                where: {
                    id: auth.user_id
                },
                select: {
                    name: true
                }
            })
            if (!user) {
                return callback.reject(12, "Internal server error");
            }

            return callback.success({ message: `Logged in as ${user.name}`});
        }, { validators: [ Validators.loggedIn ] });

        this.handle("logout", async (data, callback, io) => {
            await io.request.session.destroy(); 
        }, { validators: [ Validators.loggedIn ] });

        this.handle("createRoom", async (data, callback) => {
            const auth: Auth = data.auth;

            const params: {
                name?: string,
                config?: string
            } = data.body;

            if (!params.name) {
                return callback.reject(1, "Missing name parameter");
            }

            if (!params.config) {
                return callback.reject(1, "Missing config name parameter");
            }

            {
                const room = await db.room.findFirst({
                    select: {
                        id: true
                    },
                    where: {
                        name: params.name
                    }
                });
                if (room) {
                    return callback.reject(2, `Room with name ${params.name} already exists`);
                }
            }

            {
                const config = await db.roomConfiguration.findFirst({
                    where: {
                        name: params.config
                    },
                    select: {
                        name: true
                    }
                });
                if (!config) {
                    return callback.reject(2, `Room configuration with name ${params.config} does not exist`);
                }
            }

            const room = await db.room.create({
                data: {
                    name: params.name,
                    owner: { connect: { id: auth.user_id } },
                    joinCode: this.randomString(6),
                    config: { connect: { name: params.config } }
                }
            });

            return callback.success({ message: `Room ${room.name} created`, joinCode: room.joinCode });

        }, { validators: [ Validators.loggedIn ] });

        this.handle("addRoomConfig", async (data, callback) => {
            interface Params {
                name?: string,
                clientConfiguration?: any
            }

            const params = data.body as Params;

            if (!params.name) {
                return callback.reject(1, "Need name parameter");
            }

            if (!params.clientConfiguration) {
                return callback.reject(1, "Need clientConfiguration parameter");
            }

            {
                const rc = await db.roomConfiguration.findFirst({ where: {name: params.name}, select: {name: true}});
                if (rc) {
                    return callback.reject(2, `Room configuration ${params.name} already exists`);
                }
            }

            await db.roomConfiguration.create({
                data: {
                    name: params.name,
                    clientConfiguration: params.clientConfiguration
                }
            });
        }, { validators: [ Validators.loggedIn ] });

        this.handle("watchRoom", async (data, callback) => {
            interface Params {
                id?: number
            };

            const params = data.body as Params;
            const auth = data.auth as Auth;

            if (params.id === undefined) {
                return callback.reject(1, "Need id param");
            }

            const room = await db.room.findFirst({
                where: {
                    id: params.id
                }
            });
            
            if (!room) {
                return callback.reject(2, "Room not found");
            }

            if (room.userId != auth.user_id) {
                return callback.reject(2, "You do not have permissions to watch this room");
            }


            const token = randomUUID();
            
            const watchConfig: WatchConfig = {
                room_id: room.id
            };

            watchTokens.set(token, watchConfig);

            return callback.success({
                watchToken: token
            });

        }, { validators: [ Validators.loggedIn ] });
    }
}