#!/bin/bash

if ! [ -x "$(command -v docker)" ]; then

  echo "docker 不存在或者服务未启动"
  exit
fi

echo "docker 服务正常"

if ! [ -x "$(command -v docker-compose)" ]; then

  echo "docker-compose 异常或者未安装"
  exit
fi

echo "docker-compose 正常"

echo "下面配置服务项"

operate="up"

if [ -n "$1" ]; then
operate=$1
fi

operateArr=("up" "start" "stop" "rm")

# shellcheck disable=SC2199
# shellcheck disable=SC2076
if ! [[ " ${operateArr[@]} " =~ " ${operate} " ]]; then
  echo "参数无效"
  exit
fi

read -p "数据库名称 (jiyi): " dbname
read -p "root用户密码(123456): " dbpassword

if [ -z "$dbname" ]; then
dbname=jiyi
fi

if [ -z "$dbpassword" ]; then
dbpassword=123456
fi

export DBNAME=$dbname
export DBPASSWORD=$dbpassword

echo "下面开始部署"
(chmod +x "install_project.sh")
(docker-compose -f jyphp-compose.yml "${operate}")
