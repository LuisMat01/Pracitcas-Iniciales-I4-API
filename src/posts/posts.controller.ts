import { Controller, Get, Post, Body, Param, Query, UseGuards, Req } from '@nestjs/common';
import { PostsService } from './posts.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';
import { CreatePostDto } from './dto/create-post.dto';

@UseGuards(JwtAuthGuard)
@Controller('posts')
export class PostsController {
  constructor(private postsService: PostsService) {}

  // Obtener todas las publicaciones con filtros opcionales
  @Get()
  findAll(
    @Query('courseId') courseId?: string,
    @Query('professorId') professorId?: string,
    @Query('courseName') courseName?: string,
    @Query('professorName') professorName?: string,
  ) {
    return this.postsService.findAll({
      courseId: courseId ? Number(courseId) : undefined,
      professorId: professorId ? Number(professorId) : undefined,
      courseName,
      professorName,
    });
  }

  // Obtener una publicación
  @Get(':id')
  findOne(@Param('id') id: string) {
    return this.postsService.findOne(Number(id));
  }

  // Crear publicación
  @Post()
  create(@Req() req: any, @Body() dto: CreatePostDto) {
    return this.postsService.create(req.user.id, dto);
  }
}