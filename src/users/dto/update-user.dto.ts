import { IsEmail, IsOptional, IsString, MinLength } from 'class-validator';

export class UpdateUserDto {
  @IsOptional()
  @IsString({ message: 'El nombre debe ser texto' })
  nombres?: string;

  @IsOptional()
  @IsString({ message: 'El apellido debe ser texto' })
  apellidos?: string;

  @IsOptional()
  @IsEmail({}, { message: 'El correo no es válido' })
  email?: string;

  @IsOptional()
  @MinLength(8, { message: 'La contraseña debe tener mínimo 8 caracteres' })
  password?: string;
}