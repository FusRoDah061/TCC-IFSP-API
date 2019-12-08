# Atualizar símbolos para AWS S3
use prancha_comunicacao;

update simbolo
set arquivo = concat('https://tcc-ifsp-simbolos.s3.us-east-2.amazonaws.com/categorias/pessoas/', SUBSTRING_INDEX(arquivo, '/', -1))
where id_categoria = 1;

update simbolo
set arquivo = concat('https://tcc-ifsp-simbolos.s3.us-east-2.amazonaws.com/categorias/verbos/', SUBSTRING_INDEX(arquivo, '/', -1))
where id_categoria = 2;

update simbolo
set arquivo = concat('https://tcc-ifsp-simbolos.s3.us-east-2.amazonaws.com/categorias/substantivos/', SUBSTRING_INDEX(arquivo, '/', -1))
where id_categoria = 3;

update simbolo
set arquivo = concat('https://tcc-ifsp-simbolos.s3.us-east-2.amazonaws.com/categorias/qualificadores/', SUBSTRING_INDEX(arquivo, '/', -1))
where id_categoria = 4;

update simbolo
set arquivo = concat('https://tcc-ifsp-simbolos.s3.us-east-2.amazonaws.com/categorias/elementos_sociais/', SUBSTRING_INDEX(arquivo, '/', -1))
where id_categoria = 5;

update simbolo
set arquivo = concat('https://tcc-ifsp-simbolos.s3.us-east-2.amazonaws.com/categorias/letras_numeros/', SUBSTRING_INDEX(arquivo, '/', -1))
where id_categoria = 6;