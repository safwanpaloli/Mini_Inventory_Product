$files = Get-ChildItem -Path "app\Http\Requests" -Filter "*.php"
foreach ($file in $files) {
    $content = Get-Content $file.FullName
    
    # Change authorize() to return true
    $content = $content -replace "return false;", "return true;"
    
    # Add rules based on filename
    if ($file.Name -match "Product") {
        $rules = "return ['name' => 'required|string|min:3|max:255', 'base_price' => 'required|numeric|min:0', 'category_id' => 'required|exists:categories,id', 'variants' => 'required|array'];"
    } elseif ($file.Name -match "AttributeValue") {
        $rules = "return ['value' => 'required|string|min:3|max:255'];"
    } else {
        $rules = "return ['name' => 'required|string|min:3|max:255'];"
    }
    
    $content = $content -replace "return \[\];", $rules
    Set-Content -Path $file.FullName -Value $content
}
