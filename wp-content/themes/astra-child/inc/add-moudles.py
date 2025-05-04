#!/usr/bin/env python3
"""
最简化的WordPress功能模块生成器 - 使用绝对路径
"""

import os

def main():
    # 获取用户输入
    module_name = input("请输入功能名称: ")
    
    # 生成模块ID (将空格替换为连字符并转为小写)
    module_id = module_name.lower().replace(' ', '-')
    
    # 创建简单的PHP内容
    content = f'''<?php
/**
 * 功能名称: {module_name}
 */

// 防止直接访问文件
if (!defined('ABSPATH')) {{
    exit;
}}
'''
    
    # 获取脚本所在目录的绝对路径
    script_dir = os.path.dirname(os.path.abspath(__file__))
    
    # 在脚本所在目录创建文件
    file_path = os.path.join(script_dir, f'{module_id}.php')
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"✓ 文件已创建: {file_path}")

if __name__ == "__main__":
    main()