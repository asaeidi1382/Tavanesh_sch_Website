using System.Collections.Generic;

public class NewsItem
{
    public int Id;
    public string Title;
    public string Date;
    public string Image;
    public string Text;
}

public static class NewsData
{
    public static List<NewsItem> List = new List<NewsItem>
    {
        new NewsItem {
            Id = 1,
            Title = "نمونه خبر اول",
            Date = "۲۰ آذر ۱۴۰۴",
            Image = "",
            Text = "این یک متن نمونه برای خبر اول است."
        },
        new NewsItem {
            Id = 2,
            Title = "نمونه خبر دوم",
            Date = "۲۲ آذر ۱۴۰۴",
            Image = "",
            Text = "این یک متن نمونه برای خبر دوم است."
        }
    };
}
