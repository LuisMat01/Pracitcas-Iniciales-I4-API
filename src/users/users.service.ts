import { Injectable, NotFoundException, ForbiddenException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { UpdateUserDto } from './dto/update-user.dto';
import * as bcrypt from 'bcrypt';

@Injectable()
export class UsersService {
  constructor(private prisma: PrismaService) {}

  // Buscar usuario por registroAcademico
  async findByRegistro(registroAcademico: string) {
    const user = await this.prisma.user.findUnique({
      where: { registroAcademico },
      select: {
        id: true,
        registroAcademico: true,
        nombres: true,
        apellidos: true,
        email: true,
        createdAt: true,
        approvedCourses: {
          include: {
            course: true,
          },
        },
      },
    });

    if (!user) throw new NotFoundException('Usuario no encontrado');
    return user;
  }

  // Ver mi perfil
  async getMyProfile(userId: number) {
    const user = await this.prisma.user.findUnique({
      where: { id: userId },
      select: {
        id: true,
        registroAcademico: true,
        nombres: true,
        apellidos: true,
        email: true,
        createdAt: true,
        approvedCourses: {
          include: {
            course: true,
          },
        },
      },
    });

    if (!user) throw new NotFoundException('Usuario no encontrado');
    return user;
  }

  // Editar mi perfil (no puede cambiar registroAcademico)
  async updateMyProfile(userId: number, dto: UpdateUserDto) {
    const data: any = { ...dto };

    if (dto.password) {
      data.password = await bcrypt.hash(dto.password, 10);
    }

    return this.prisma.user.update({
      where: { id: userId },
      data,
      select: {
        id: true,
        registroAcademico: true,
        nombres: true,
        apellidos: true,
        email: true,
        createdAt: true,
      },
    });
  }

  // Ver cursos aprobados de cualquier usuario
  async getApprovedCourses(registroAcademico: string) {
    const user = await this.prisma.user.findUnique({
      where: { registroAcademico },
      select: {
        nombres: true,
        apellidos: true,
        approvedCourses: {
          include: {
            course: {
              select: {
                id: true,
                nombre: true,
                creditos: true,
              },
            },
          },
        },
      },
    });

    if (!user) throw new NotFoundException('Usuario no encontrado');

    const totalCreditos = user.approvedCourses.reduce(
      (sum, ac) => sum + ac.course.creditos, 0
    );

    return {
      usuario: `${user.nombres} ${user.apellidos}`,
      totalCreditos,
      cursos: user.approvedCourses.map((ac) => ac.course),
    };
  }

  // Agregar curso aprobado (solo mi perfil)
  async addApprovedCourse(userId: number, courseId: number) {
    const course = await this.prisma.course.findUnique({ where: { id: courseId } });
    if (!course) throw new NotFoundException('Curso no encontrado');

    return this.prisma.approvedCourse.create({
      data: { userId, courseId },
      include: { course: true },
    });
  }

  // Eliminar curso aprobado (solo mi perfil)
  async removeApprovedCourse(userId: number, courseId: number) {
    const approvedCourse = await this.prisma.approvedCourse.findUnique({
      where: { userId_courseId: { userId, courseId } },
    });

    if (!approvedCourse) throw new NotFoundException('Curso no encontrado en tu lista');

    return this.prisma.approvedCourse.delete({
      where: { userId_courseId: { userId, courseId } },
    });
  }
}