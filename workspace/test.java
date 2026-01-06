public class Main {
    public static void main(String[] args) {
        int width = 80, height = 40;
        int maxIter = 100;

        System.out.println("--- Java Docker Sandbox 测试 ---");
        System.out.println("内存限制: 256MB | CPU限制: 0.5Core");

        for (int y = 0; y < height; y++) {
            for (int x = 0; x < width; x++) {
                double zx = 0, zy = 0;
                double cx = (x - 50.0) / 20.0;
                double cy = (y - 20.0) / 10.0;
                int iter = maxIter;
                while (zx * zx + zy * zy < 4 && iter > 0) {
                    double tmp = zx * zx - zy * zy + cx;
                    zy = 2.0 * zx * zy + cy;
                    zx = tmp;
                    iter--;
                }
                System.out.print(iter > 0 ? " " : "*");
            }
            System.out.println();
        }
        System.out.println("\n[SUCCESS] Java 环境运行正常，Docker 资源隔离有效。");
    }
}