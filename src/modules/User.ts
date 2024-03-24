import { createIs } from "typia";
import ServerModule, { RequestValidator } from "../ServerModule";
import { Hash, Hmac, createHash, createHmac, randomBytes } from "crypto";
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
        const db = this.server.db;
        const log = this.server.instance.log;

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
                name?: string
            } = data.body;

            if (!params.name) {
                return callback.reject(1, "Missing name parameter");
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

            const room = await db.room.create({
                data: {
                    name: params.name,
                    owner: { connect: { id: auth.user_id } },
                    joinCode: this.randomString(6),
                    client_configuration: {}
                }
            });

            return callback.success({ message: `Room ${room.name} created`, joinCode: room.joinCode });

        }, { validators: [ Validators.loggedIn ] });

    }
}