drop schema if exists prancha_comunicacao;

create schema prancha_comunicacao character set UTF8 collate utf8_bin;

use prancha_comunicacao;

drop table if exists usuario;
create table usuario (
	id int primary key auto_increment not null,
    hid varchar(10) null unique,
    nome varchar(255) not null,
    email varchar(255) not null,
    senha char(255) not null,
    api_token varchar(80) null unique,
    created_at datetime default current_timestamp(),
    updated_at datetime default current_timestamp(),
    unique (email)
);

drop table if exists prancha;
create table prancha (
	id int primary key auto_increment not null,
    hid varchar(10) null unique,
    id_usuario int  not null,
    nome varchar(255) not null,
    created_at datetime default current_timestamp(),
    updated_at datetime default current_timestamp(),
    foreign key (id_usuario) references usuario(id) on delete cascade,
    unique (nome, id_usuario)
);

drop table if exists categoria;
create table categoria (
	id int primary key auto_increment not null,
    hid varchar(10) null unique,
    nome varchar(255) not null,
    cor varchar(17) not null default '#ffffff',
    created_at datetime default current_timestamp(),
    updated_at datetime default current_timestamp(),
    unique (nome)
);

drop table if exists simbolo;
create table simbolo (
	id int primary key auto_increment not null,
    hid varchar(10) null unique,
    id_usuario int,
    id_categoria int not null,
    nome varchar(255) not null,
    arquivo varchar(255) not null comment 'Diretório onde o arquivo está guardado.',
    tipo int not null default 1  comment 'Tipo de símbolo: 1 = imagem, 2 = vídeo.', /*0 = imagem, 1 = vídeo*/
    created_at datetime default current_timestamp(),
    updated_at datetime default current_timestamp(),
    unique (id_usuario, id_categoria, nome, tipo, arquivo),
    foreign key (id_usuario) references usuario(id) on delete cascade,
    foreign key (id_categoria) references categoria(id) on delete cascade,
    check(tipo = 1 or tipo = 2)
);

drop table if exists rel_simbolo_prancha;
create table rel_simbolo_prancha (
	id_prancha int not null,
    id_simbolo int not null,
    primary key (id_prancha, id_simbolo),
    foreign key (id_prancha) references prancha(id) on delete cascade,
    foreign key (id_simbolo) references simbolo(id) on delete cascade
);

drop table if exists pedido_rec_senha;
create table pedido_rec_senha (
	id int primary key auto_increment not null,
    token varchar(40) not null unique,
	email varchar(255) not null,
    created_at datetime default current_timestamp() comment 'Data de expiração é calculada a partir da criação. 12 horas de validade.',
    updated_at datetime default current_timestamp(),
    foreign key (email) references usuario(email) on delete cascade on update cascade
);

create user prancha_adm identified by 'Cncy5CSRH?2tPt2S';
grant all on prancha_comunicacao.* to prancha_adm;