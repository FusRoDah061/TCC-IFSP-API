# Configurações

## AWS

---

Nesta seção serão apresentadas as configuração utilizadas para hospedar a API nos serviços AWS.

São usados os seguintes serviços:

- **EC2**: Utilizado para hospedar o sistema *Laravel*;
- **RDS**: Utilizado para hospedar a base de dados *MySQL*;
- **S3**: Utilizado para armazenar os arquivos dos símbolos;
- **SES**: Utilizado apenas para envios de e-mails de recuperação de senha.

### EC2

#### Criação da instância

Nada de especial deve ser feito durante a criação da instância. Apenas alguns pontos de atenção:
- Verifique a VPC em qua a instância será criada. Os outros serviços (RDS, SES e S3) deverão utilizar esta mesma VPC;
- Nas configurações do grupo de segurança, lembre de adicionar uma entrada permitindo tráfego HTTP com source `0.0.0.0/0`.

#### Configurações da VM

Para disponibilizar o sistema serão necessários dois componentes básicos: servidor web Apache e PHP. Instruções de instalação destes podem ser encontradas [aqui](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04). Também será necessário [instalar o Git](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-16-04) para obter os repositórios.

Após isso, entre na pasta do Apache (`/var/www/html`) e crie uma pasta `tcc` para o sistema.

Dentro desta pasta crie mais duas pastas:

- `api`: onde será feito checkout desse repositório;
- `site`: onde será feito checkout do [front-end](https://github.com/FusRoDah061/TCC-IFSP-SITE).

Após isso:

- Entre na pasta `api`;
- Faça o checkout do repositório: `git clone https://github.com/FusRoDah061/TCC-IFSP-API.git .`;
- e dê permissão ao usuário nas pastas do laravel: `chmod -R 777 storage bootstrap/cache`.

##### Configuração do Apache

Este processo usou como referência:

- [Steps for configuring Laravel on Apache HTTP Server](https://phpraxis.wordpress.com/2016/08/02/steps-for-configuring-laravel-on-apache-http-server/)
- [How To Set Up Apache Virtual Hosts on Ubuntu 16.04](https://www.digitalocean.com/community/tutorials/how-to-set-up-apache-virtual-hosts-on-ubuntu-16-04)

Será necessário criar dois virtual-hosts (vhost): um para a API e outro para o front-end ([instruções na sua página](https://github.com/FusRoDah061/TCC-IFSP-SITE)):

- Entre no diretório de hosts do Apache: `cd /etc/apache2/sites-available`;
- Crie um arquivo `.conf` para o vhost da API, por exemplo, `tcc.conf`;
- Dentro desse arquivo, entre o seguinte código:

```xml

<VirtualHost *:80>
	ServerAdmin webmaster@email.com
	ServerName url.instancia.ec2.com
	ServerAlias 1.1.1.1
	DocumentRoot /var/www/html/tcc/api/public

	<Directory /var/www/html/tcc/api/public/ >
		Options Indexes FollowSymLinks MultiViews
		AllowOverride All
		Order allow,deny
		allow from all
		Require all granted
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/error.log
	CustomLog ${APACHE_LOG_DIR}/access.log combined
</VirtualHost>

```

- Alguns pontos de atenção:
  - `DocumentRoot` indica o diretório onde estão os arquivos de entrada do sistema, nessa caso, o arquivo `index.php`;
  - Note a `/` ao final do diretório em `Directory`. Mantenha essa barra no final.
  - `ServerAdmin`, `ServerName` e `ServerAlias` devem ser preenchidos de acordo com suas configurações;
  - `ServerName` será o **DNS público** da instância EC2;
  - `ServerAlias` será o **endereço IP** da instância EC2.
- Habilite o novo vhost no Apache: `sudo a2ensite tcc.conf`;
- Habilite o `mod_rewrite` no Apache: `sudo a2enmod rewrite`;
- Reinicie o Apache: `sudo service apache2 restart`.

Caso seja necessário manter o firewall da VM ativo, tenha certeza de permitir conexões **SSH** e **HTTP** (ou HTTPS).

O controle de conexões poderá ser feito pelo grupos de segurança do AWS, mas se o firewall da máquina não liberar essas conexões, a configuração do AWS não terá efeito.

### RDS

Crie uma instância de banco de dados MySQL 5.7.

Durante a criação, atente-se para:

- Utilizar a mesma VPC onde a instância EC2 foi criada;
- Na seção `Connectivity`, caso deseje acessar o bd remotamente, marque a opção `Publicly accessible`. O acesso poderá ser restringido por IP nas regras do grupo de seguarança da VPC.
- Ainda na seção `Connectivity`, pode ser criado um novo grupo de segurança para o RDS, de modo a flexibilizar as configurações de acesso do RDS, EC2, etc.

### S3

Crie um bucket com **acesso público desbloqueado**.

Nas configurações de CORS, adicione a regra: 

```xml

<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
<CORSRule>
    <AllowedOrigin>ta.incluirtecnologia.com.br</AllowedOrigin>
    <AllowedMethod>GET</AllowedMethod>
    <AllowedMethod>POST</AllowedMethod>
    <AllowedMethod>PUT</AllowedMethod>
    <AllowedMethod>DELETE</AllowedMethod>
    <AllowedHeader>*</AllowedHeader>
</CORSRule>
</CORSConfiguration>

```

Onde `ta.incluirtecnologia.com.br` é o endereço do [front-end](https://github.com/FusRoDah061/TCC-IFSP-SITE) por onde os usuário acessam.

No bucket criado serão enviados todos os arquivos de símbolos, disponibilizados em [arquivo zip](https://github.com/FusRoDah061/TCC-IFSP-API/releases). Mantenha a mesma estrutura de diretórios do arquivo:

```plain

- users
- categorias
 |- elementos_sociais
 |- letras_numeros
 |- pessoas
 |- qualificadores
 |- substantivos
 |- verbos

```

É possível utilizar as [ferramentas de linha de comando do S3](https://docs.aws.amazon.com/pt_br/cli/latest/userguide/cli-services-s3-commands.html) para enviar todos os arquivos.

### SES

Realize as configurações de acordo com sua necessidade. Em nível mais básico, basta permitir um endereço de envio de e-mails.

### VPCs e Security Groups

Configure de acordo com a necessidade.

É interessante restringir o acesso a base de dados apenas para a instância do EC2, e se necessário, para um host de acesso remoto. O mesmo vale para o EC2: liberar apenas o tráfego HTTP para todas as origens (0.0.0.0) e SSH para um host específico de acesso remoto.

## Laravel

Esta seção apresenta as configurações a serem realizadas na API

### Propriedades .env

- `APP_URL`: Endereço da instância EC2 onde a API está.
- `BASE_SITE_URL`: Endereço do site por onde os usuários acessam. **É utilizado no link de recuperação de senha enviado por e-mail.**
- `DB_CONNECTION`: Indica qual configuração de conexão de banco de dados o Laravel deve utilizar (no arquivo `config/database.php`). Nesse caso, `mysql`
- `DB_HOST`: Endereço da instância RDS onde o banco de dados foi criado.
- `DB_PORT`: Porta de acesso do banco de dados. MySQL tem como padrão a porta `3306`;
- `DB_DATABASE`: Nome do banco de dados criado no RDS.
- `DB_USERNAME`: Usuário do banco de dados criado no RDS.
- `DB_PASSWORD`: Senha do banco de dados criado no RDS.
- `MAIL_DRIVER`: Driver de e-mail a ser utilizado. Nesse caso, `ses`.
- `MAIL_ENCRYPTION`: tls
- `MAIL_FROM_ADDRESS`: Endereço remetente dos e-mails enviados. **Deve ter sido verificado no SES.**
- `MAIL_FROM_NAME`: Nome do remetente dos e-mails enviados
- `AWS_ACCESS_KEY_ID`: Chave de acesso das APIs AWS criada no IAM.
- `AWS_SECRET_ACCESS_KEY`: Chave secreta de acesso das APIs AWS criada no IAM, obtida junto da `AWS_ACCESS_KEY_ID`.
- `AWS_SES_REGION`: Região onde o serviço do SES foi criado.
- `AWS_S3_REGION`: Região onde o serviço do S3 foi criado.
- `AWS_BUCKET`: Nome do bucket S3 criado.
- `AWS_BUCKET_URL`: Endereço do bucket S3 criado.
- `HASHIDS_SALT`: Valor utilizado pelo Hashids para erar os hashs. Os HIDs nos scripts de criação do banco de dados utilizaram `80d369e3f6f9070ea288b7f34dfe78bf` como o salt. **Alterar esse valor faz necessário atualizar todos os HIDs já gerados.**
- `HASHIDS_LENGTH`: Tamanho do hash gerado pelo Hashids. **Alterar esse valor faz necessário atualizar todos os HIDs já gerados.**
- `PAGINATION_SIZE`: Tamanhos das *"páginas"* de símbolos retornadas pela API em cada busca. Por padrão, `70` símbolos são retornados.
- `RECAPTCHA_API`: Endereço da API do Recaptcha. Atualmente (Dez/2019) é `https://www.google.com/recaptcha/api/siteverify`.
- `RECAPTCHA_SECRET`: Chave de API do Recaptcha.
- `GRAVATAR_URL`: Endereço da API do Gravatar. Atualmente (Dez/2019) é `https://www.gravatar.com/avatar/`.
- `SIMBOLO_IMAGEM`: **Constante** que indica um símbolo de imagem. Valor: `1`
- `SIMBOLO_VIDEO`: **Constante** que indica um símbolo de vídeo. Valor: `2`
- `CATEGORIA_TODOS`: **Constante** que indica a categoria "Todos os simbolos". Valor: `all`
- `CATEGORIA_MEUS`: **Constante** que indica a categoria "Meus simbolos". Valor: `user`
- `IMG_FILE_SIZE_MB`: Tamanho máximo dos arquivos de imagem. **A configuração do PHP deve também permitir o upload de arquivos com o tamanho definido aqui.**
- `VIDEO_FILE_SIZE_MB`: Tamanho máximo dos arquivos de vídeo. **A configuração do PHP deve também permitir o upload de arquivos com o tamanho definido aqui.**

### Deploy com Git hooks

Vamos configurar um repositório bare na instância EC2 para fazermos *code push* de novas alterações.

Nesse repositório, vamos criar um hook `post-receive` para que, após todo push feito para a branch `master`, seja executado um script de *"deploy"*, que vai fazer o checkout das novas alterações na pasta do Apache, e executar os comandos de instalação do Composer e de cache do Artisan.

**Pré-requisitos:**
- [Apache](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04) instalado;
- [PHP](https://www.digitalocean.com/community/tutorials/how-to-install-linux-apache-mysql-php-lamp-stack-on-ubuntu-16-04) instalado;
- [PHP Composer](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) instalado.
- [Git](https://www.digitalocean.com/community/tutorials/how-to-install-git-on-ubuntu-16-04) instalado.

#### Criação do hook

Este processo usou como referência [este guia](https://www.digitalocean.com/community/tutorials/how-to-use-git-hooks-to-automate-development-and-deployment-tasks).

- Acesse a instância EC2 onde o sistema está hospedado.
- Entre na pasta `/var/www/html`.
- Crie uma pasta `git`, e dentro dela uma pasta `tcc.git`;
- Inicialize um repositório bare (não contém os arquivos de código) com o comando: `git init --bare`;
- Dentre as pastas que foram criadas, entre na pasta `hooks`;
- Devem existir vários arquivos `.sample`, que são alguns exemplos de hooks possíveis. Crie um novo arquivo chamado `post-receive` (sem extensão), esse será o nosso hook de deploy;
- Dentro desse arquivo, coloque o seguinte código:

```bash

#!/bin/bash
echo "Received push from local repo."
while read oldrev newrev ref
do
	if [[ $ref =~ .*/master$ ]];
	then
		echo "Master ref received.  Deploying master branch to production..."
		git --work-tree=/var/www/html/tcc/api --git-dir=/var/www/html/git/tcc.git checkout -f
		cd /var/www/html/tcc/api
		sudo composer install
		php artisan config:cache
	else
		echo "Ref $ref successfully received.  Doing nothing: only the master branch may be deployed on this server."
	fi
done

echo "All done here."

```

- Note o comando `git --work-tree=/var/www/html/tcc/api --git-dir=/var/www/html/git/tcc.git checkout -f`: 
  - `/var/www/html/tcc/api`: diretório onde está o repositório git comum com código da API;
  - `var/www/html/git/tcc.git`: diretório desse repositóroio bare.
- Nos comandos após este, vamos:
  - entrar no diretório que contém o código: `cd /var/www/html/tcc/api`;
  - instalar as dependências do projeto: `sudo composer install`;
  - *"cachear"* as configurações do `.env`: `php artisan config:cache`. Caso o `.env` não esteja comitado no repositório, este passo deve ser feito manualmente se necessário.
- Dê permissão total à pasta git para o usuário: `chmod -R 777 /var/www/html/git`;
- Utilize SSH para fazer push de novas alterações. No endereço do remote, adicione uma URL SSH no formato: `ssh://usuario@endereco.ec2.com:22/var/www/html/git/tcc.git` (será necessário configurar no git a chave pública de acesso SSH ao EC2).