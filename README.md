# Lsky-Pro Docker镜像

每周自动拉取最新代码构建Docker镜像

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

如果使用了Nginx反代后，如果出现无法加载图片的问题，可以根据原项目 [#317](https://github.com/lsky-org/lsky-pro/issues/317) 执行以下指令

***Tips：将\<container\>改为自己容器的名字***

```bash
docker exec -it lskypro sed -i '32 a \\\Illuminate\\Support\\Facades\\URL::forceScheme('"'"'https'"'"');' /var/www/html/app/Providers/AppServiceProvider.php
```

来手动修改容器内`AppServiceProvider.php`文件对于HTTPS的支持

原项目：[☁️兰空图床(Lsky Pro) - Your photo album on the cloud.](https://github.com/lsky-org/lsky-pro)
