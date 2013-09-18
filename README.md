# Zoek

## 欢迎使用 Zoek
简洁、优雅、易用的微型 Blog 系统，让 Blog 写作更方便、简单。

Zoek 是一个开源系统，使用 MIT 授权协议，意味着允许用于个人、公司或商业目的。

## 下载方式 （任选其一）
- 直接下载： [https://github.com/mingfunwong/zoek/archive/master.zip](https://github.com/mingfunwong/zoek/archive/master.zip)
- 通过 Git 命令： `git clone git://github.com/mingfunwong/zoek.git`

## Zoek 有什么优势？

- 简单、优雅、易用的微型 Blog 系统

- 基于 Markdown 存储文件

- 具有良好的、结构性的、简约的代码，使用 PHP 5.3 新特征

- 整个程序是 620 KB

- Zoek 结构良好，支持扩展

- Zoek 是 Pico 的分支版本

## 服务器环境要求
- PHP 5.3.0 或更新版本
- Apache mod_rewrite模块（用于支持固定链接功能）

## 如何创建内容？

Zoek 是一个基于文件的 Blog 系统，这意味着没有管理后台和数据库处理。

您只需创建 `.md` 文件，然后在文件前添加 Url 地址。例如添加了 `Url: about` 只后便可以通过 http://example.com/about 进行访问。

下面显示内容的位置和它们的相应的URL的一些例子：

<dl class="dl-horizontal">
  <dt>Url: </dt>
	<dd>http://example.com</dd>
  <dt>Url: about</dt>
	<dd>http://example.com/about</dd>
  <dt>Url: zoek/doc</dt>
	<dd>http://example.com/zoek/doc</dd>
</dl>

提示：404 页面是位于 `content/404.md`。

## 使用优雅的 Markdown 方式写作

Markdown 是一种轻量级标记语言，它允许人们使用易读易写的纯文本格式编写文档，然后转换成有效的 HTML 文档。

使用 Markdown 方式写作具有以下好处：

- 让你专注于文字而不是排版
- 格式转换方便，Markdown 的文本你可以轻松转换为 html、电子书等
- Markdown 是纯文本，所以兼容性极强，可以用所有文本编辑器打开
- Markdown 的标记语法有极好的可读性
- Markdown 也可以包含普通 HTML 内容

### 语法示例

#### 标题

可以在标题内容前输入特定数量的井号 # 来实现对应级别的 HTML 样式的标题。例如：

	# 一级标题

	#### 四级标题


#### 段落

一个段落是由一个或多个连续的行构成，段落间靠一个或以上视觉上的空行划分。一般的段落不应该用空格或制表符缩进。

	这是一个段落。它有两个句子。

	这是另一个段落。它也有 
	两个句子。

#### 图片

	![Foo](http://example.com/image.png)

#### 链接

链接可以在行内插入

	[链接文字](链接地址)
	例子： [Markdown](http://zh.wikipedia.com/wiki/Markdown)


更多 Markdown 语法请浏览 [Markdown 語法說明](http://markdown.tw/)

## 使用注释块

在 Markdown 的文本文件顶部，请放置一个注释块，并在页面中指定某些属性。例如：

	/*

	Title: Zoek - 简洁优雅的 Blog 系统
	
	Description: 这个是一个 Description 标签
	
	Url: index
	
	Author: Zoek
	
	Naviname: Home
	
	Date: 2013-01-10
	
	Category: page
	
	Robots: noindex,nofollow

	*/


## 主题

你可以创建你 Zoek 安装主题在主题文件夹。 Zoek 使用
[Twig](http://twig.sensiolabs.org/documentation) 作为模版引擎。 您可以通过 `config.php ` 的 `$config['theme']` 选择您的主题。

所有的主题都必须包含一个 `index.html` 文件定义主题的 HTML 结构。以下是可以使用在你的主题使用的变量：

* `{{ config }}`
* `{{ base_dir }}`
* `{{ base_url }}`
* `{{ theme_dir }}`
* `{{ theme_url }}`
* `{{ site_title }}`
* `{{ meta }}`
	* `{{ meta.title }}`
	* `{{ meta.category }}`
	* `{{ meta.naviname }}`
	* `{{ meta.url }}`
	* `{{ meta.description }}`
	* `{{ meta.author }}`
	* `{{ meta.date }}`
	* `{{ meta.date_formatted }}`
	* `{{ meta.robots }}`
	* `{{ meta.order }}`
* `{{ content }}`
* `{{ pages }}`
	* `{{ pages.file }}` 
	* `{{ pages.meta }}` 
	* `{{ pages.content }}` 
	* `{{ pages.excerpt }}` 
* `{{ category }}`
* `{{ current_page }}`
* `{{ is_front_page }}` 

可以使用以下方式输出全部文章：

<pre>&lt;ul class=&quot;nav&quot;&gt;
	{% for page in pages %}
	&lt;li&gt;&lt;a href=&quot;{{ page.meta.url }}&quot;&gt;{{ page.meta.title }}&lt;/a&gt;&lt;/li&gt;
	{% endfor %}
&lt;/ul&gt;</pre>

### 设置

请浏览 `config.php` 取得所有设置

	// 站点标题
	$config['site_title'] = 'Zoek';
	
	// 站点描述
	$config['site_description'] = '简约优雅的 Blog 系统';
	
	// 站点地址 (例如：http://example.com )
	$config['base_url'] = 'http://zoek.com'; 
	
	// 使用主题
	$config['theme'] = 'blog';
	
	// 日期格式
	$config['date_format'] = 'M d, Y \a\t g:i a';
	
	// 下面是配置在插件中使用
	$config['pages_length'] = 1;
	
	// 以下是 Twig 模板配置
	// $config['twig_config'] = array(
	//     'cache' => CACHE_DIR
	// );
	
	// 页面排序依据 (请选择 alpha 或 date )
	$config['pages_order_by'] = 'date';
	
	// 页面排序方式 (请选择 asc 或 desc )
	$config['pages_order'] = 'desc';
	
	// 页面摘要长度 (默认：50 )
	$config['excerpt_length'] = 50;

### 帮助链接

- [Zoek 官方网站](http://zoek.mingfunwong.com/)
- [Zoek 帮助文档](http://zoek.mingfunwong.com/doc)
