<?php
/*
Template Name: 工作时间计算器
*/

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <div class="entry-content">
                <!DOCTYPE html>
                <html lang="zh-CN">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>工作时间计算器</title>
                    <style>
                        .calculator-container {
                            max-width: 600px;
                            margin: 30px auto;
                            padding: 20px;
                            background-color: #f8f9fa;
                            border-radius: 8px;
                            box-shadow: 0 0 10px rgba(0,0,0,0.1);
                        }
                        .form-group {
                            margin-bottom: 20px;
                        }
                        label {
                            display: block;
                            margin-bottom: 5px;
                            font-weight: bold;
                        }
                        input {
                            width: 100%;
                            padding: 8px;
                            border: 1px solid #ddd;
                            border-radius: 4px;
                            box-sizing: border-box;
                        }
                        button {
                            background-color: #0073aa;
                            color: white;
                            padding: 10px 20px;
                            border: none;
                            border-radius: 4px;
                            cursor: pointer;
                        }
                        button:hover {
                            background-color: #005177;
                        }
                        #result {
                            margin-top: 20px;
                            padding: 15px;
                            background-color: #fff;
                            border-radius: 4px;
                            display: none;
                        }
                        .calculation-formula {
                            background-color: #f0f8ff;
                            padding: 10px;
                            border-radius: 5px;
                            margin: 10px 0;
                        }
                        .final-result {
                            font-weight: bold;
                            font-size: 1.1em;
                            color: #0073aa;
                            background-color: #e6f7ff;
                            padding: 10px;
                            border-radius: 5px;
                            margin-top: 15px;
                        }
                    </style>
                </head>
                <body>
                    <div class="calculator-container">
                        <h2>工作时间计算器</h2>
                        <form id="workHoursForm">
                            <div class="form-group">
                                <label for="startDate">开始日期：</label>
                                <input type="date" id="startDate" required>
                            </div>
                            <div class="form-group">
                                <label for="endDate">结束日期：</label>
                                <input type="date" id="endDate" required>
                            </div>
                            <div class="form-group">
                                <label for="holidays">法定节假日天数：</label>
                                <input type="number" id="holidays" min="0" value="0" required>
                                <small>请根据下方参考，计算区间内的法定节假日数量</small>
                            </div>
                            <button type="submit">计算</button>
                        </form>
                        <div id="result"></div>
                        
                        <div class="holiday-reference">
                            <h3>法定节假日参考</h3>
                            <p>全体公民放假的节日一年中共有13天，分别是：</p>
                            <ul>
                                <li>元旦，放假1天（1月1日）</li>
                                <li>春节，放假4天（农历除夕、正月初一至初三）</li>
                                <li>清明节，放假1天（农历清明当日）</li>
                                <li>劳动节，放假2天（5月1日、2日）</li>
                                <li>端午节，放假1天（农历端午当日）</li>
                                <li>中秋节，放假1天（农历中秋当日）</li>
                                <li>国庆节，放假3天（10月1日至3日）</li>
                            </ul>
                            <p><strong>请手动计算日期区间内包含的法定节假日天数，并在上方输入框中填写。</strong></p>
                        </div>
                    </div>

                    <script>
                        document.getElementById('workHoursForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const startDate = new Date(document.getElementById('startDate').value);
                            const endDate = new Date(document.getElementById('endDate').value);
                            const holidays = parseInt(document.getElementById('holidays').value);
                            
                            if (startDate > endDate) {
                                alert('开始日期不能晚于结束日期！');
                                return;
                            }

                            let workDays = 0;         // 工作日（不含周末）
                            let weekendDays = 0;      // 周末天数
                            const currentDate = new Date(startDate);
                            
                            // 计算工作日和周末天数
                            while (currentDate <= endDate) {
                                const day = currentDate.getDay();
                                if (day === 0 || day === 6) {
                                    // 周末
                                    weekendDays++;
                                } else {
                                    // 工作日
                                    workDays++;
                                }
                                currentDate.setDate(currentDate.getDate() + 1);
                            }

                            // 计算区间总天数
                            const totalDays = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                            
                            // 实际工作日（减去法定节假日）
                            const actualWorkDays = Math.max(0, workDays - holidays);
                            
                            // 计算正常工作时间（工作日 * 8小时）
                            const workHours = actualWorkDays * 8;
                            
                            // 年度加班时间上限（固定为432小时）
                            const overtimeHours = 432;
                            
                            // 计算综合计时工时总计
                            const totalHours = workHours + overtimeHours;

                            // 格式化日期显示
                            const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
                            const formattedStartDate = startDate.toLocaleDateString('zh-CN', dateOptions);
                            const formattedEndDate = endDate.toLocaleDateString('zh-CN', dateOptions);

                            const resultDiv = document.getElementById('result');
                            resultDiv.style.display = 'block';
                            resultDiv.innerHTML = `
                                <h3>📆 您输入的综合计时计算周期是：</h3>
                                <p>${formattedStartDate} 至 ${formattedEndDate}</p>
                                 <h3> 🧙‍♂️ 我掐指一算，该周期内：</h3>
                                <p>总天数：${totalDays}天</p>
                                <p>周末天数：${weekendDays}天</p>
                                <p>法定节假日：${holidays}天</p>
                                <p>实际应工作天数：${actualWorkDays}天</p>
                                
                                <div class="calculation-formula">
                                    <strong>💼周期内正常工作时间 </strong><br>
                                     =(总天数 ${totalDays}天 - 周末天数 ${weekendDays}天 - 法定节假日 ${holidays}天) × 8小时<br>
                                    = ${actualWorkDays}天 × 8小时 = ${workHours}小时
                                </div>
                                
                                <div class="calculation-formula">
                                    年度加班时间上限 = 12个月 × 36小时/月 = ${overtimeHours}小时
                                </div>
                                
                                <div class="final-result">
                                    ⏱️ 综合计时工时总时间 = 正常工作时间 ${workHours}小时 + 年度加班时间上限 ${overtimeHours}小时 = ${totalHours}小时
                                </div>
                            `;
                        });
                    </script>
                </body>
                </html>
            </div>
        </article>
    </main>
</div>

<?php get_footer(); ?>