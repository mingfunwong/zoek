# Zoek

## 欢迎使用 Zoek
Zoek 是一个优雅易用的博客系统，Markdown 方式写作，让你专注于文字而不是排版。

Zoek 是一个开源系统，使用 MIT 授权协议，意味着允许用于个人、公司或商业目的。

## 下载方式 （任选其一）
- 直接下载： [https://github.com/mingfunwong/zoek/archive/master.zip](https://github.com/mingfunwong/zoek/archive/master.zip)
- 通过 Git 命令： `git clone git://github.com/mingfunwong/zoek.git`

## Zoek 有什么优点？

- 基于极简主义设计的、优雅易用的博客系统

- 基于 Markdown 格式存储文件

- 具有良好的、结构性的、简约的代码，使用 PHP 5.3 新特征

- 小巧，易于二次开发，整个程序大小约 550 KB

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

在 Markdown 的文本文件顶部，请放置一个注释块。例如：

	/*

	Title: Zoek - 优雅易用的博客系统
	
	Url: usezoek

	Date: 2014-01-10
	*/


## 设置

初次使用 Zoek 时请重设配置文件。

一、站点设置

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

二、留言板设置

打开 `theme/page.htm` 找到：

    <div id="disqus_thread"></div>
    <script type="text/javascript">
        /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
        var disqus_shortname = 'mingfunwong'; // required: replace example with your forum shortname

        /* * * DON'T EDIT BELOW THIS LINE * * */
        (function() {
            var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
            dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    </script>

请替换成您的留言板代码。建议使用 [Disqus](http://disqus.com/) 、 [多说](http://duoshuo.com/) 。

## 相关链接

- [Zoek 官方网站](http://mingfunwong.com/zoek)
- [Zoek 开源项目](https://github.com/mingfunwong/zoek)
