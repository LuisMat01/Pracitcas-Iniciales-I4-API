import { Injectable, NotFoundException } from '@nestjs/common';
import { PrismaService } from '../prisma/prisma.service';
import { CreateCommentDto } from './dto/create-comment.dto';

@Injectable()
export class CommentsService {
  constructor(private prisma: PrismaService) {}

  async create(userId: number, postId: number, dto: CreateCommentDto) {
    const post = await this.prisma.post.findUnique({ where: { id: postId } });
    if (!post) throw new NotFoundException('Publicación no encontrada');

    return this.prisma.comment.create({
      data: {
        mensaje: dto.mensaje,
        userId,
        postId,
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
      },
    });
  }
}