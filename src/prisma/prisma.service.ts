import 'dotenv/config';
import { Injectable } from '@nestjs/common';
import { PrismaClient } from '../../generated/prisma/client';
import { PrismaMariaDb } from '@prisma/adapter-mariadb';

@Injectable()
export class PrismaService extends PrismaClient {
  constructor() {
    const adapter = new PrismaMariaDb({
      host: process.env.DB_HOST ?? 'localhost',
      port: Number(process.env.DB_PORT) ?? 3306,
      user: process.env.DB_USER ?? 'admin',
      password: process.env.DB_PASSWORD ?? 'admin1234',
      database: process.env.DB_NAME ?? 'usac_db',
      allowPublicKeyRetrieval: true,
    });

    super({ adapter });
  }
}