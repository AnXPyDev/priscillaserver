import { configDotenv } from "dotenv";
import fastify, { FastifyReply, FastifyRequest } from "fastify";
import { PrismaClient } from "@prisma/client";

configDotenv();

const server = fastify({
    logger: true
});

const prisma = new PrismaClient();
const con = prisma.$connect();

server.post<{
    Params: { action: string },
    Body: { test: string }
}>("/action/:action", async (request, reply) => {
    const { action } = request.params;
    reply.send({ code: 0, action, data: request.body });
})


server.listen({ port: Number.parseInt(process.env.FASTIFY_PORT!!) }, (err, address) => {
    if (err) {
        server.log.error(err);
        process.exit(1);
    }
});