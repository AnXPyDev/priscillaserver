import { FastifyRequest, FastifyReply } from "fastify";
import Server from "./Server";

export type RequestValidator = (request: FastifyRequest, callback: { accept: (data?: object) => void, reject: (code?: number, message?: string) => void }) => void;
export type RequestHandler = (data: any, callback: { success: (data?: any) => void, reject: (code?: number, message?: string) => void }, io: {
    request: FastifyRequest,
    reply: FastifyReply
}) => Promise<any>;

export default abstract class ServerModule {
    server!: Server;
    route: string;
    constructor(route: string) {
        this.route = route;
    }

    attach(server: Server) {
        this.server = server;
    }

    handle(endpoint: string, handler: RequestHandler, options: {
        validators?: RequestValidator[]
    } = {}) {
        this.server.instance.post(`/${this.route}/${endpoint}`, async (request, reply) => {
            const reject = (code: number = 1, message: string = "Unknown error occured") => {
                this.server.instance.log.info({}, `Request at ${endpoint} rejected "${message}"`);
                reply.send({ code, message });
            };

            try {
                let data: object = {
                    body: request.body
                };

                for (const validator of options.validators ?? []) {
                    validator(request, { 
                        accept: (rdata) => { Object.assign(data, rdata); }, 
                        reject
                    });

                    if (reply.sent) {
                        return;
                    }
                }
                
                await handler(data, {
                    success: (data?) => { reply.send(Object.assign({ code: 0 }, data)); },
                    reject
                }, {
                    request, reply
                });

                if (!reply.sent) {
                    reply.send({ code: 0 });
                }
            } catch (e) {
                reject(12, "Internal server error");
            }
        });
    }

    abstract init(): void;
}