import { Controller, Get, Put, Post, Delete, Param, Body, UseGuards, Req } from '@nestjs/common';
import { UsersService } from './users.service';
import { JwtAuthGuard } from '../auth/jwt-auth.guard';
import { UpdateUserDto } from './dto/update-user.dto';

@UseGuards(JwtAuthGuard) // todas las rutas protegidas
@Controller('users')
export class UsersController {
  constructor(private usersService: UsersService) {}

  // Ver mi perfil
  @Get('me')
  getMyProfile(@Req() req: any) {
    return this.usersService.getMyProfile(req.user.id);
  }

  // Editar mi perfil
  @Put('me')
  updateMyProfile(@Req() req: any, @Body() dto: UpdateUserDto) {
    return this.usersService.updateMyProfile(req.user.id, dto);
  }

  // Buscar usuario por registroAcademico
  @Get(':registroAcademico')
  findByRegistro(@Param('registroAcademico') registroAcademico: string) {
    return this.usersService.findByRegistro(registroAcademico);
  }

  // Ver mis cursos aprobados
  @Get('me/courses')
  getMyApprovedCourses(@Req() req: any) {
    return this.usersService.getApprovedCourses(req.user.registroAcademico);
  }

  // Ver cursos aprobados de otro usuario
  @Get(':registroAcademico/courses')
  getApprovedCourses(@Param('registroAcademico') registroAcademico: string) {
    return this.usersService.getApprovedCourses(registroAcademico);
  }

  // Agregar curso aprobado a mi perfil
  @Post('me/courses/:courseId')
  addApprovedCourse(@Req() req: any, @Param('courseId') courseId: string) {
    return this.usersService.addApprovedCourse(req.user.id, Number(courseId));
  }

  // Eliminar curso aprobado de mi perfil
  @Delete('me/courses/:courseId')
  removeApprovedCourse(@Req() req: any, @Param('courseId') courseId: string) {
    return this.usersService.removeApprovedCourse(req.user.id, Number(courseId));
  }
}