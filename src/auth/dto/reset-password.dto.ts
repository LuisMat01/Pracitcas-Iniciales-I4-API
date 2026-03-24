import { IsEmail, IsNotEmpty, IsString, MinLength } from 'class-validator';

export class ResetPasswordDto {
  @IsNotEmpty({ message: 'El registro académico es requerido' })
  @IsString()
  registroAcademico!: string;

  @IsEmail({}, { message: 'El correo no es válido' })
  email!: string;

  @IsNotEmpty()
  @MinLength(8, { message: 'La nueva contraseña debe tener mínimo 8 caracteres' })
  newPassword!: string;
}