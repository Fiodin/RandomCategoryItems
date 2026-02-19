# RandomCategoryItems
Random list of  Wiki categories with a "more" button

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
| Parameter     | Werte                        | Example                         | If not set        |
| ------------- | ---------------------------- | ------------------------------- | ----------------- |
| `category`    | Name of the category         | `category="categoryname"`       |                   |
| `count`       | Number of items in list      | `count="10"`                    |                   |
| `border`      | `yes` / `no`                 | `border="no"`                   | Borders are on    |
| `bordercolor` | Hex, rgb(), Farbname         | `bordercolor="#aabbcc"`         |                   |
| `bgcolor`     | Hex, rgb(), Farbname         | `bgcolor="#f5f5f5"`             |                   |
| `textcolor`   | Hex, rgb(), Farbname         | `textcolor="#333333"`           |                   |
| `radius`      | `round`, `square`, oder Wert | `radius="8px"`                  |                   |
| `fontsize`    | em, px, %                    | `fontsize="0.85em"`             |                   |
| `bullets`     | `yes` / `no`                 | `bullets="no"`                  | No bullets        |
| `layout`      | `horizontal` / `vertical`    | `layout="horizontal"`           | Horizontal        |
| `more`        | Wikilink to category         | `more="Kategorie:Categoryname"` | Nothing is showed |
| `morelabel`   | Rename the link              | `morelabel="See more →"`        | Nothing is showed |
