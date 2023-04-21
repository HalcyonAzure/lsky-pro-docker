# Lsky-Pro Docker镜像

每天自动拉取最新代码构建Docker镜像

## 使用方法

```docker
docker run -d \
    --name lsky-pro \
    --restart unless-stopped \
    -p 8089:8089 \
    -v $PWD/lsky/web:/var/www/html \
    -e WEB_PORT=8089 \
    halcyonazure/lsky-pro-docker:latest
```

## 环境变量

目前该容器只有一个环境变量：`WEB_PORT`，用于指定容器内的`Apache`监听的端口，默认为`8089`，如果需要修改的话可以在启动容器时添加`-e WEB_PORT=8089`来指定端口

### Windows内以`WSL`的方式部署`Docker`容器

按照 [#13](https://github.com/HalcyonAzure/lsky-pro-docker/issues/13) 的反馈来看，如果在`Windows`内创建容器出现了将文件挂载于`WSL`内，然后出现了重启系统文件未识别的情况，可以将映射目录修改为类似`\\wsl$\Ubuntu\path-mount-lsky\`的形式

## 反代HTTPS

如果使用了Nginx反代后，如果出现无法加载图片的问题，可以根据原项目 [#317](https://github.com/lsky-org/lsky-pro/issues/317) 执行以下指令来手动修改容器内`AppServiceProvider.php`文件对于HTTPS的支持

***Tips：将lskypro改为自己容器的名字***

```bash
docker exec -it lskypro sed -i '32 a \\\Illuminate\\Support\\Facades\\URL::forceScheme('"'"'https'"'"');' /var/www/html/app/Providers/AppServiceProvider.php
```

## Docker-Compose部署参考

使用`MySQL`来作为数据库的话可以参考原项目 [#256](https://github.com/lsky-org/lsky-pro/issues/256) 来创建`docker-compose.yaml`，参考内容如下：

```yaml
version: '3'
services:
  lskypro:
    image: halcyonazure/lsky-pro-docker:latest
    restart: unless-stopped
    hostname: lskypro
    container_name: lskypro
    environment:
      - WEB_PORT=8089
    volumes:
      - /data/lsky/web:/var/www/html/
    ports:
      - "8089:8089"
    networks:
      - lsky-net

  mysql-lsky:
    image: mysql:5.7.22
    restart: unless-stopped
    # 主机名，可作为"数据库连接地址"
    hostname: mysql-lsky
    # 容器名称
    container_name: mysql-lsky
    # 修改加密规则
    command: --default-authentication-plugin=mysql_native_password
    volumes:
      - $PWD/mysql/data:/var/lib/mysql
      - $PWD/mysql/conf:/etc/mysql
      - $PWD/mysql/log:/var/log/mysql
    environment:
      MYSQL_ROOT_PASSWORD: lAsWjb6rzSzENUYg # 数据库root用户密码，自行修改
      MYSQL_DATABASE: lsky-data # 可作为"数据库名称/路径"
    networks:
      - lsky-net

networks:
  lsky-net: {}
```

原项目：[☁️兰空图床(Lsky Pro) - Your photo album on the cloud.](https://github.com/lsky-org/lsky-pro)

## 手动备份/升级

如果需要迁移数据库/手动升级`Lsky-Pro`，可以参考官方文档：[升级｜Lsky Pro](https://docs.lsky.pro/docs/free/v2/quick-start/upgrade.html)，来备份主要文件以进行恢复/升级
