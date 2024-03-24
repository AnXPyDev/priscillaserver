import { PrismaClient } from "@prisma/client";
import fastify, { FastifyInstance } from "fastify";
import ServerModule from "./ServerModule";
import UserModule from "./modules/User";
import fastifyCookie from "@fastify/cookie";
import fastifySession from "@fastify/session";

export default class Server {
    instance: FastifyInstance;
    modules: ServerModule[] = [];
    db: PrismaClient = new PrismaClient();

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
    }

    initModules() {
        this.modules.push(
            new UserModule()
        );

        for (const module of this.modules) {
            module.attach(this);
            module.init();
        }
    }

}