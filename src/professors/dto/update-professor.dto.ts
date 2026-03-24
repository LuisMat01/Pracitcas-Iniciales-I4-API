import { IsOptional, IsString } from 'class-validator';

export class UpdateProfessorDto {
  @IsOptional()
  @IsString()
  nombres?: string;

  @IsOptional()
  @IsString()
  apellidos?: string;
}