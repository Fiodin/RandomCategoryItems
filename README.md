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

or you can customize ist with a "more" link with an own label:
```html
<randomcategoryitems 
  category="categoryname" 
  count="10"
  border="no"
  layout="horizontal"
  more="Kategorie:Category" 
  morelabel="More to see →" />
```
