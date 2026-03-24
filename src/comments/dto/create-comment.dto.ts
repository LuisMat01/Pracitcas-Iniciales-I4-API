import { IsNotEmpty, IsString } from 'class-validator';

export class CreateCommentDto {
  @IsNotEmpty({ message: 'El comentario es requerido' })
  @IsString()
  mensaje!: string;
}