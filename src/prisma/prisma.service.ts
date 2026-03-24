import { Injectable } from '@nestjs/common';
import { PrismaClient } from '../../generated/prisma/client';
import { PrismaPlanetScale } from '@prisma/adapter-planetscale';

@Injectable()
export class PrismaService extends PrismaClient {
    constructor() {
        const adapter = new PrismaPlanetScale({ url: process.env.DATABASE_URL });
        super({ adapter });
    }
}
