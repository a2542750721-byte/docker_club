
USER="root"
HOST="112.124.96.86"

TARGET="/www/Docker_Web"

echo "🚀 正在传输文件..."


tar --exclude='.git' --exclude='.trae' --exclude='workspace' -czf - . | ssh $USER@$HOST "mkdir -p $TARGET && tar -xzf - -C $TARGET"


echo "🔧 正在校正服务器文件权限..."

ssh $USER@$HOST "chown -R root:root $TARGET && chmod -R 755 $TARGET"

echo "✅ 部署完美成功！"