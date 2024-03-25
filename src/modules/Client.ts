import { Socket } from "socket.io";
import ServerModule from "../ServerModule";
import { randomUUID } from "crypto";
import EventEmitter from "events";

export default class ClientModule extends ServerModule {
    clientConnections = new Map<string, Socket>();

    constructor() {
        super("client");
    };

    init(): void {
        const supervisor_socks = this.server.socks.of("/supervisor");
        const db = this.server.db;
        const events = this.server.events;

        supervisor_socks.on("connection", async (socket) => {
            const auth: { secret?: string } = socket.handshake.auth;
            if (!auth.secret) {
                socket.send("No auth provided");
                return socket.disconnect();
            }

            const client = await db.client.findFirst({
                where: {
                    secret: auth.secret
                },
                select: { id: true, name: true, roomId: true }
            });

            if (!client) {
                socket.send("Invalid secret");
                return socket.disconnect();
            }

            socket.send("Connected successfully");

            this.clientConnections.set(auth.secret, socket);

            const signal = `ROOM:${client.roomId}`;

            events.emit(signal, "message", `Client ${client.id} ${client.name} connected supervisor`);

            socket.on("integrityEvent", async (integrityEvent: object) => {
                console.log(`${signal} ${client.id} ${client.name} integrityEvent ${JSON.stringify(integrityEvent)}`);
                events.emit(signal, "integrityEvent", client.id, integrityEvent);
                await db.integrityEvent.create({
                    data: {
                        client: { connect: { id: client.id } },
                        data: integrityEvent
                    }
                });
            });

            socket.on("disconnect", () => {
                events.emit(signal, "message", `Client ${client.id} ${client.name} disconnected supervisor`);
            });
        });


        this.handle("joinRoom", async (data, callback) => {
            interface Params {
                joinCode?: string,
                name?: string
            };

            const params = data.body as Params;

            if (!params.name) {
                return callback.reject(1, "Need name param");
            }

            if (!params.joinCode) {
                return callback.reject(1, "Need joinCode param");
            }

            const room = await db.room.findFirst({
                where: {
                    joinCode: params.joinCode
                },
                include: {
                    config: true
                }
            });

            if (!room) {
                return callback.reject(2, "No room with such joinCode");
            }

            const secret = randomUUID();

            const client = await db.client.create({
                data: {
                    name: params.name,
                    secret,
                    room: { connect: { id: room.id } }
                }
            });

            events.emit(`ROOM:${room.id}`, "message", `Client ${client.id} ${client.name} joined`);

            return callback.success({
                message: `Connected to room ${room.name}`,
                secret,
                clientConfiguration: room.config.clientConfiguration
            });
        });

        this.handle("echo", async (data, callback) => {
            return callback.success({"message": data.body.message});
        });
    }
}