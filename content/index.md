/*

Title: Zoek - 简洁优雅的 CMS 系统

Description: 这个是一个 Description 标签

Url: 

Author: Zoek

Naviname: Home

Date: 2013-01-10

Category: page

Robots: 

*/

恭喜你，你已经成功安装 Zoek。 Zoek 简洁优雅的 CMS 系统。

### 如何创建内容？

Zoek 是一个基于文件的 CMS 系统，这意味着没有管理后台和数据库处理。您只需创建 `.md` 文件即可成为一个页面。例如，本页面是 `content/index.md`。 

下面显示内容的位置和它们的相应的URL的一些例子：

<table>
	<thead>
		<tr><th>文件位置</th><th>URL</th></tr>
	</thead>
	<tbody>
		<tr><td>content/index.md</td><td>/</td></tr>
		<tr><td>content/sub.md</td><td>/sub</td></tr>
		<tr><td>content/sub/index.md</td><td>/sub (same as above)</td></tr>
		<tr><td>content/sub/page.md</td><td>/sub/page</td></tr>
		<tr><td>content/a/very/long/url.md</td><td>/a/very/long/url</td></tr>
	</tbody>
</table>

提示：404 页面是 `content/404.md`.

### Markup 文件标记

Markdown 写作方法请浏览 [Markdown 語法說明](http://markdown.tw/)。Markdown 也可以包含普通 HTML 内容。

在顶部的文本文件，你可以放置一个注释块，并在页面中指定某些属性。例如：

	/ *
	Title: 欢迎
	Description:  这个是一个 Description 标签
	Author: Joe Bloggs
	Date: 2013/01/01
	Robots: noindex,nofollow
	*/

这些值将被包含在 `{{ '{{' }} meta }}` 可变的主题（见下文）。

也有一些变量，您可以使用您的文本文件：

* <code>&#37;base_url&#37;</code> - 您的网站 URL

### 主题

你可以创建你 Zoek 安装主题在主题文件夹。 Zoek 使用
[Twig](http://twig.sensiolabs.org/documentation) 作为模版引擎。 您可以通过 `config.php ` 的 `$config['theme']` 设置您的主题。

所有的主题都必须包含一个 `index.html` 文件定义主题的 HTML 结构。以下是可以使用在你的主题使用的变量：

* `{{ '{{' }} config }}` - 是您设置 config.php 的值 (例如： `{{ '{{' }} config.theme }}` = "default")
* `{{ '{{' }} base_dir }}` - 您网站的根目录的路径
* `{{ '{{' }} base_url }}` - 您网站的网站的 URL
* `{{ '{{' }} theme_dir }}` - 主题目录的路径
* `{{ '{{' }} theme_url }}` - 主题目录的 URL
* `{{ '{{' }} site_title }}` - 网站标题 (在 config.php 设置)
* `{{ '{{' }} meta }}` - 从当前页包含 meta 值
	* `{{ '{{' }} meta.title }}`
	* `{{ '{{' }} meta.description }}`
	* `{{ '{{' }} meta.author }}`
	* `{{ '{{' }} meta.date }}`
	* `{{ '{{' }} meta.date_formatted }}`
	* `{{ '{{' }} meta.robots }}`
* `{{ '{{' }} content }}` - 当前页面的内容 (使用 Markdown 编写)
* `{{ '{{' }} pages }}` - 收集在你的网站的所有内容
	* `{{ '{{' }} page.title }}`
	* `{{ '{{' }} page.url }}`
	* `{{ '{{' }} page.author }}`
	* `{{ '{{' }} page.date }}`
	* `{{ '{{' }} page.date_formatted }}`
	* `{{ '{{' }} page.content }}`
	* `{{ '{{' }} page.excerpt }}`
* `{{ '{{' }} prev_page }}` - 上一页
* `{{ '{{' }} current_page }}` - 当前页
* `{{ '{{' }} next_page }}` - 下一页
* `{{ '{{' }} is_front_page }}` 

页面也可以使用：

<pre>&lt;ul class=&quot;nav&quot;&gt;
	{% for page in pages %}
	&lt;li&gt;&lt;a href=&quot;{{ '{{' }} page.url }}&quot;&gt;{{ '{{' }} page.title }}&lt;/a&gt;&lt;/li&gt;
	{% endfor %}
&lt;/ul&gt;</pre>

### 设置

`config.php` 文件列出所有的设置。

### 帮助文档

Zoek 帮助文档

[Zoek](http://mingfunwong.com/zoek/)
