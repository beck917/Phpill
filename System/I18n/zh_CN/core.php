<?php 

$lang = array
(
	'there_can_be_only_one' => '每个请求页面只允许一个 Phpill 的实例化',
	'uncaught_exception'    => '未捕获 %s 异常：%s 于文件 %s 的行 %s',
	'invalid_method'        => '无效方法 %s 调用于 %s',
	'invalid_property'      => '属性 %s 不存在于 %s 类中。',
	'log_dir_unwritable'    => '日志目录不可写：%s',
	'resource_not_found'    => '请求的 %s，%s，不存在',
	'invalid_filetype'      => '在视图配置文件内请求的文件类型，.%s，不允许',
	'view_set_filename'     => '在调用 render 之前您必须设置视图文件名',
	'no_default_route'      => '请在 config/routes.php 文件设置默认的路由参数值',
	'no_controller'         => 'Phpill 没有找到处理该请求的控制器：%s',
	'page_not_found'        => '您请求的页面 %s，不存在。',
	'stats_footer'          => '页面加载 {execution_time} 秒，使用内存 {memory_usage}。程序生成 Phpill v{phpill_version}。',
	'error_file_line'       => '<tt>%s <strong>[%s]：</strong></tt>',
	'stack_trace'           => '堆栈跟踪',
	'generic_error'         => '无法完成请求',
	'errors_disabled'       => '您可以返回<a href="%s">首页</a>或者<a href="%s">重新尝试</a>。',

	// 驱动
	'driver_implements'     => '%s 驱动在类 %s 中必须继承 %s 接口',
	'driver_not_found'      => '%s 驱动在类 %s 中没有发现',

	// 资源名称
	'config'                => '配置文件',
	'controller'            => '控制器',
	'helper'                => '辅助函数',
	'library'               => '库',
	'driver'                => '驱动',
	'model'                 => '模型',
	'view'                  => '视图',
);