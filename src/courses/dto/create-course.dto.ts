import { IsNotEmpty, IsInt, IsString, Min } from 'class-validator';

export class CreateCourseDto {
  @IsNotEmpty({ message: 'El nombre del curso es requerido' })
  @IsString()
  nombre!: string;

  @IsInt({ message: 'Los créditos deben ser un número entero' })
  @Min(1, { message: 'Los créditos deben ser mayor a 0' })
  creditos!: number;
}