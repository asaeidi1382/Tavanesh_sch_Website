<%@ Page Language="C#" %>
<%@ Import Namespace="System.Linq" %>

<%
int id;
if (!int.TryParse(Request.QueryString["id"], out id))
{
    Response.Write("خبر نامعتبر است");
    Response.End();
}

var news = NewsData.List.FirstOrDefault(n => n.Id == id);
if (news == null)
{
    Response.Write("خبر یافت نشد");
    Response.End();
}
%>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8" />
<title><%= news.Title %></title>
</head>

<body>
<h1><%= news.Title %></h1>
<div><%= news.Date %></div>
<p><%= news.Text %></p>
</body>
</html>
