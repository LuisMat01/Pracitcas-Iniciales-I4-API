import { IsNotEmpty, IsString } from 'class-validator';

export class CreateProfessorDto {
  @IsNotEmpty({ message: 'El nombre es requerido' })
  @IsString()
  nombres!: string;

  @IsNotEmpty({ message: 'El apellido es requerido' })
  @IsString()
  apellidos!: string;
}