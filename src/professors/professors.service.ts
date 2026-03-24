import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateProfessorDto } from './dto/create-professor.dto';
import { UpdateProfessorDto } from './dto/update-professor.dto';

@Injectable()
export class ProfessorsService {
  constructor(private prisma: PrismaService) {}

  findAll() {
    return this.prisma.professor.findMany({
      orderBy: { apellidos: 'asc' },
    });
  }

  async findOne(id: number) {
    const professor = await this.prisma.professor.findUnique({
      where: { id },
      include: {
        posts: {
          orderBy: { createdAt: 'desc' },
          include: {
            user: {
              select: {
                id: true,
                nombres: true,
                apellidos: true,
                registroAcademico: true,
              },
            },
            comments: {
              orderBy: { createdAt: 'asc' },
              include: {
                user: {
                  select: {
                    id: true,
                    nombres: true,
                    apellidos: true,
                  },
                },
              },
            },
          },
        },
      },
    });

    if (!professor) throw new NotFoundException('Catedrático no encontrado');
    return professor;
  }

  create(dto: CreateProfessorDto) {
    return this.prisma.professor.create({ data: dto });
  }

  async update(id: number, dto: UpdateProfessorDto) {
    const professor = await this.prisma.professor.findUnique({ where: { id } });
    if (!professor) throw new NotFoundException('Catedrático no encontrado');

    return this.prisma.professor.update({
      where: { id },
      data: dto,
    });
  }

  async remove(id: number) {
    const professor = await this.prisma.professor.findUnique({ where: { id } });
    if (!professor) throw new NotFoundException('Catedrático no encontrado');

    return this.prisma.professor.delete({ where: { id } });
  }
}