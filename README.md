# Lsky-Pro Docker镜像

每天自动拉取最新代码构建Docker镜像

## 使用方法

```docker
docker run -d \
    --name lsky-pro \
    --restart unless-stopped \
    -p 9080:80 \
    -v /path-to-data:/var/www/html \
    halcyonazure/lsky-pro-docker:latest
```

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
    volumes:
      - /data/lsky:/var/www/html
    ports:
      - "9080:80"
    networks:
      - lsky-net

  mysql-lsky:
    image: mysql:5.7.22
    restart: unless-stopped
    # 主机名，可作为子网域名填入安装引导当中
    hostname: mysql-lsky
    # 容器名称
    container_name: mysql-lsky
    # 修改加密规则
    command: --default-authentication-plugin=mysql_native_password
    volumes:
      - /data/lsky/mysql/data:/var/lib/mysql
      - /data/lsky/mysql/conf:/etc/mysql
      - /data/lsky/mysql/log:/var/log/mysql
    environment:
      MYSQL_ROOT_PASSWORD: lAsWjb6rzSzENUYg # 数据库root用户密码
      MYSQL_DATABASE: lsky-data # 给lsky-pro用的数据库名称
    networks:
      - lsky-net

networks:
  lsky-net:
```

原项目：[☁️兰空图床(Lsky Pro) - Your photo album on the cloud.](https://github.com/lsky-org/lsky-pro)
