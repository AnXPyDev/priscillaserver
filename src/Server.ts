import { Client, PrismaClient } from "@prisma/client";
import fastify, { FastifyInstance } from "fastify";
import ServerModule from "./ServerModule";
import UserModule from "./modules/User";
import fastifyCookie from "@fastify/cookie";
import fastifySession from "@fastify/session";
import ClientModule from "./modules/Client";
import { Server as SocketServer } from "socket.io";
import { EventEmitter } from "events";

export default class Server {
    instance: FastifyInstance;
    modules: ServerModule[] = [];
    db: PrismaClient = new PrismaClient();
    socks = new SocketServer();
    events = new EventEmitter();

    client!: ClientModule;
    user!: UserModule;

    constructor() {
        this.instance = fastify({
            logger: {
                file: "server.log"
            }
        });

        this.instance.register(fastifyCookie);
        this.instance.register(fastifySession, {secret: 'jfasdklfjasdlkfjasdlkfjaklfjlkasfjlkajflkajflkasdjflkajfkldasjfklasdjfkladjfklasdjfklasdjfklasdjfkla'});

        this.initModules();

        this.instance.listen({ port: Number.parseInt(process.env.FASTIFY_PORT ?? "0") });
        this.socks.listen(Number.parseInt(process.env.SOCKET_PORT ?? "0" ));
    }

    initModules() {
        this.client = new ClientModule();
        this.user = new UserModule();

        this.modules.push(
            this.client,
            this.user
        );

        for (const module of this.modules) {
            module.attach(this);
            module.init();
        }
    }

}