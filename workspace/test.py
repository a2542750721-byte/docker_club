# 一个简单的BMI（身体质量指数）计算器
def calculate_bmi(weight, height):
    """
    计算BMI指数
    weight: 体重(kg)
    height: 身高(m)
    """
    bmi = weight / (height ** 2)
    return round(bmi, 2)

def get_bmi_category(bmi):
    """根据BMI值判断体型分类"""
    if bmi < 18.5:
        return "偏瘦"
    elif 18.5 <= bmi < 24:
        return "正常"
    elif 24 <= bmi < 28:
        return "偏胖"
    else:
        return "肥胖"

# 主程序
def main():
    print("=== BMI计算器 ===")
    
    try:
        # 获取用户输入
        weight = float(input("请输入体重(kg): "))
        height = float(input("请输入身高(m): "))
        
        if weight <= 0 or height <= 0:
            print("请输入有效的正数！")
            return
        
        # 计算BMI
        bmi = calculate_bmi(weight, height)
        category = get_bmi_category(bmi)
        
        # 输出结果
        print(f"\n计算结果:")
        print(f"BMI指数: {bmi}")
        print(f"体型分类: {category}")
        
        # 给出建议
        print(f"\n健康建议:")
        if category == "偏瘦":
            print("建议适当增加营养，进行力量训练")
        elif category == "正常":
            print("保持良好生活习惯！")
        elif category == "偏胖":
            print("建议控制饮食，增加运动")
        else:
            print("建议咨询专业医生，制定减重计划")
            
    except ValueError:
        print("请输入有效的数字！")

# 运行程序
if __name__ == "__main__":
    main()