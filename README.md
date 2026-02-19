# RandomCategoryItems
Random list of  Wiki categories with a "more" button.
The list will randomnize itself once per day. The cache TTL is set to 86400 seconds (= 24 hours). The exact time depends on when the page is first accessed after a cache expiry, so not at a fixed time of day, but relative to the last render timestamp.

# Install
Simply add the files into the folder 'RandomCategoryItems'.
Then activate it in the 'LocalSettings.php':
```bash
wfLoadExtension( 'RandomCategoryItems' );
```

# Integration
Simply add this anywhere inside a wiki text:
```html
<randomcategoryitems category="categoryname" count="10" />
```

or you can customize ist with some more options:
```html
<randomcategoryitems 
  category="categoryname" 
  count="10"
  border="no"
  layout="horizontal"
  more="Kategorie:Category" 
  morelabel="More to see →" />
```
A list of the parameters
| Parameter     | Werte                        | Example                         | If not set                           |
| ------------- | ---------------------------- | ------------------------------- | ------------------------------------ |
| `category`    | Name of the category         | `category="categoryname"`       |                                      |
| `count`       | Number of items in list      | `count="10"`                    |                                      |
| `border`      | `yes` / `no`                 | `border="no"`                   | Borders are on                       |
| `bordercolor` | Hex, rgb(), Farbname         | `bordercolor="#aabbcc"`         |                                      |
| `bgcolor`     | Hex, rgb(), Farbname         | `bgcolor="#f5f5f5"`             |                                      |
| `textcolor`   | Hex, rgb(), Farbname         | `textcolor="#333333"`           |                                      |
| `radius`      | `round`, `square`, oder Wert | `radius="8px"`                  |                                      |
| `fontsize`    | em, px, %                    | `fontsize="0.85em"`             |                                      |
| `bullets`     | `yes` / `no`                 | `bullets="no"`                  | No bullets                           |
| `layout`      | `horizontal` / `vertical`    | `layout="vertical"`             | Horizontal                           |
| `more`        | Wikilink to category         | `more="Wikilink"`               | Link to the category from `category` |
| `morelabel`   | Rename the link              | `morelabel="See more →"`        | `Alle Einträge →` is showed          |
