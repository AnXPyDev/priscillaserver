import { FastifyInstance, FastifyReply, FastifyRequest } from "fastify";

export default abstract class Action<Body, Reply, Params = undefined> {
    route: string;

    constructor(route: string) {
        this.route = route;
    }

    attach(server: FastifyInstance) {
    }
}