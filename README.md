# Zoek

## 欢迎使用 Zoek
Zoek 是一个优雅易用的博客系统，Markdown 方式写作，让你专注于文字而不是排版。

## 下载方式 （任选其一）
- 直接下载： [https://github.com/mingfunwong/zoek/archive/master.zip](https://github.com/mingfunwong/zoek/archive/master.zip)
- 通过 Git 命令： `git clone git://github.com/mingfunwong/zoek.git`

## Zoek 有什么优点？

- 基于极简主义设计的、优雅易用的博客系统

- 基于 Markdown 格式存储文件

- 使用 PHP 5.3 新特征，具有良好简约的代码风格

- 小巧优雅，易于二次开发

## 服务器环境要求
- PHP 5.3.0 或更新版本
- Apache mod_rewrite模块（用于支持固定链接功能）

## 如何创建内容？

Zoek 是一个基于文件的博客系统，这意味着没有管理后台和数据库处理。

您只需在 `/log` 目录创建 `.md` 文件，然后在文件前添加 Url 地址。例如添加了 `Url: about` 只后便可以通过 http://example.com/about 进行访问。

下面显示内容的位置和它们的相应的URL的一些例子：

<dl class="dl-horizontal">
  <dt>Url: first</dt>
	<dd>http://example.com/first</dd>
  <dt>Url: about</dt>
	<dd>http://example.com/about</dd>
</dl>


## Markdown 方式写作

Markdown 是一种轻量级标记语言，它允许人们使用易读易写的纯文本格式编写文档，然后转换成有效的 HTML 文档。


## 使用注释块

在 Markdown 的文本文件顶部，请放置一个注释块。例如：

	/*

	Title: Zoek - 优雅易用的博客系统
	
	Url: usezoek

	Date: 2014-01-10
	*/


## 设置

初次使用 Zoek 时请重设配置文件。

### 站点设置

打开 `config.ini` 配置文件进行相关设置，默认设置如下。

	[globals]
	; 系统配置
	; 0 部署环境 3 开发环境
	DEBUG=0
	; 模版目录
	UI=theme/
	; 自动转义
	ESCAPE=false
	
	; 应用配置
	; 网站标题
	site_name=Zoek 站点
	; 底部链接
	footer_link[0][name]=Zoek 驱动
	footer_link[0][link]=https://github.com/mingfunwong/zoek


## 相关链接

- [Zoek 官方网站](http://mingfunwong.com/zoek)
- [Zoek 开源项目](https://github.com/mingfunwong/zoek)
