import { Injectable, NotFoundException, BadRequestException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreatePostDto } from './dto/create-post.dto';

@Injectable()
export class PostsService {
  constructor(private prisma: PrismaService) {}

  // Obtener todas las publicaciones ordenadas por fecha
  async findAll(filters?: {
    courseId?: number;
    professorId?: number;
    courseName?: string;
    professorName?: string;
  }) {
    const where: any = {};

    if (filters?.courseId) {
      where.courseId = filters.courseId;
    }

    if (filters?.professorId) {
      where.professorId = filters.professorId;
    }

    if (filters?.courseName) {
      where.course = {
        nombre: { contains: filters.courseName },
      };
    }

    if (filters?.professorName) {
      where.professor = {
        OR: [
          { nombres: { contains: filters.professorName } },
          { apellidos: { contains: filters.professorName } },
        ],
      };
    }

    return this.prisma.post.findMany({
      where,
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
        course: {
          select: {
            id: true,
            nombre: true,
          },
        },
        professor: {
          select: {
            id: true,
            nombres: true,
            apellidos: true,
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
                registroAcademico: true,
              },
            },
          },
        },
      },
    });
  }

  // Obtener una publicación por ID
  async findOne(id: number) {
    const post = await this.prisma.post.findUnique({
      where: { id },
      include: {
        user: {
          select: {
            id: true,
            nombres: true,
            apellidos: true,
            registroAcademico: true,
          },
        },
        course: {
          select: {
            id: true,
            nombre: true,
          },
        },
        professor: {
          select: {
            id: true,
            nombres: true,
            apellidos: true,
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
                registroAcademico: true,
              },
            },
          },
        },
      },
    });

    if (!post) throw new NotFoundException('Publicación no encontrada');
    return post;
  }

  // Crear publicación
  async create(userId: number, dto: CreatePostDto) {
    // Debe tener curso O catedrático, no ambos ni ninguno
    if (!dto.courseId && !dto.professorId) {
      throw new BadRequestException('Debes seleccionar un curso o un catedrático');
    }

    if (dto.courseId && dto.professorId) {
      throw new BadRequestException('Solo puedes seleccionar un curso o un catedrático, no ambos');
    }

    if (dto.courseId) {
      const course = await this.prisma.course.findUnique({ where: { id: dto.courseId } });
      if (!course) throw new NotFoundException('Curso no encontrado');
    }

    if (dto.professorId) {
      const professor = await this.prisma.professor.findUnique({ where: { id: dto.professorId } });
      if (!professor) throw new NotFoundException('Catedrático no encontrado');
    }

    return this.prisma.post.create({
      data: {
        mensaje: dto.mensaje,
        userId,
        courseId: dto.courseId ?? null,
        professorId: dto.professorId ?? null,
      },
      include: {
        user: {
          select: {
            id: true,
            nombres: true,
            apellidos: true,
            registroAcademico: true,
          },
        },
        course: true,
        professor: true,
      },
    });
  }
}