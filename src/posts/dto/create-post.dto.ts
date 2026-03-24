import { IsNotEmpty, IsOptional, IsInt, IsString } from 'class-validator';

export class CreatePostDto {
  @IsNotEmpty({ message: 'El mensaje es requerido' })
  @IsString()
  mensaje!: string;

  @IsOptional()
  @IsInt({ message: 'El curso debe ser un ID válido' })
  courseId?: number;

  @IsOptional()
  @IsInt({ message: 'El catedrático debe ser un ID válido' })
  professorId?: number;
}