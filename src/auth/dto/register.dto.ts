import { IsEmail, IsNotEmpty, IsString, MinLength } from 'class-validator';

export class RegisterDto {
  @IsNotEmpty({ message: 'El registro académico es requerido' })
  @IsString()
  registroAcademico!: string;

  @IsNotEmpty({ message: 'El nombre es requerido' })
  @IsString()
  nombres!: string;

  @IsNotEmpty({ message: 'El apellido es requerido' })
  @IsString()
  apellidos!: string;

  @IsEmail({}, { message: 'El correo no es válido' })
  email!: string;

  @IsNotEmpty()
  @MinLength(8, { message: 'La contraseña debe tener mínimo 8 caracteres' })
  password!: string;
}