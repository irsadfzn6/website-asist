<?php
defined('APK') or exit('No access');

/**
 * Robust DOCX to HTML parser & renderer for Surat Keluar
 */
function renderDocxToHtml($docxPath, $replacements = []) {
    if (!file_exists($docxPath)) {
        return false;
    }
    
    $zip = new ZipArchive();
    if ($zip->open($docxPath) !== TRUE) {
        return false;
    }
    
    // 1. Extract images from word/media/
    $images = [];
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $entry = $zip->getNameIndex($i);
        if (strpos($entry, 'word/media/') === 0) {
            $imgData = $zip->getFromIndex($i);
            $mime = 'image/png';
            $ext = strtolower(pathinfo($entry, PATHINFO_EXTENSION));
            if ($ext === 'jpg' || $ext === 'jpeg') $mime = 'image/jpeg';
            elseif ($ext === 'gif') $mime = 'image/gif';
            
            $base64 = 'data:' . $mime . ';base64,' . base64_encode($imgData);
            $images[basename($entry)] = $base64;
        }
    }
    
    // 2. Read Word Header XML files (header1.xml, header2.xml, header3.xml)
    $headerHtml = '';
    for ($h = 1; $h <= 3; $h++) {
        $hXml = $zip->getFromName("word/header{$h}.xml");
        if ($hXml !== false) {
            $headerHtml .= parseDocxContentXml($hXml, $images, $replacements);
        }
    }
    
    // 3. Read Word Document XML
    $docXml = $zip->getFromName('word/document.xml');
    $zip->close();
    
    if (!$docXml) {
        return false;
    }
    
    $bodyHtml = parseDocxContentXml($docXml, $images, $replacements);
    
    return '<div class="docx-rendered-container">' 
         . (!empty($headerHtml) ? '<div class="docx-header-section">' . $headerHtml . '</div>' : '')
         . '<div class="docx-body-section">' . $bodyHtml . '</div>'
         . '</div>';
}

function parseDocxContentXml($xmlContent, $images = [], $replacements = []) {
    if (empty($xmlContent)) return '';
    
    // Apply string replacements first
    foreach ($replacements as $search => $replace) {
        $xmlContent = str_replace($search, htmlspecialchars((string)$replace, ENT_QUOTES, 'UTF-8'), $xmlContent);
    }
    
    // Strip namespaces for easier parsing
    $cleanXml = preg_replace('/xmlns:[^=]+="[^"]+"/', '', $xmlContent);
    
    // Parse paragraphs and tables
    // Match elements in sequence
    preg_match_all('/<(w:p|w:tbl)[^>]*>.*?<\/\1>/s', $cleanXml, $blocks);
    
    $htmlOutput = '';
    foreach ($blocks[0] as $block) {
        if (strpos($block, '<w:tbl') === 0) {
            $htmlOutput .= parseDocxTable($block, $images);
        } else {
            $htmlOutput .= parseDocxParagraph($block, $images);
        }
    }
    
    return $htmlOutput;
}

function parseDocxParagraph($pXml, $images = []) {
    // Alignment
    $align = 'left';
    if (preg_match('/<w:jc\s+w:val="([^"]+)"/', $pXml, $mAlign)) {
        $alignVal = $mAlign[1];
        if ($alignVal === 'center') $align = 'center';
        elseif ($alignVal === 'right') $align = 'right';
        elseif ($alignVal === 'both' || $alignVal === 'distribute') $align = 'justify';
    }
    
    // Check for images in paragraph
    $imgHtml = '';
    if (preg_match('/<w:drawing>/', $pXml) || preg_match('/<v:imagedata/', $pXml)) {
        foreach ($images as $base64Src) {
            $imgHtml .= "<div style='text-align:$align; margin: 5px 0;'><img src='$base64Src' style='max-width:100%; max-height:160px; object-fit:contain;'></div>";
            break;
        }
    }
    
    // Parse runs <w:r>
    preg_match_all('/<w:r[^>]*>(.*?)<\/w:r>/s', $pXml, $rMatches);
    $pContent = '';
    
    foreach ($rMatches[1] as $rXml) {
        $isBold = preg_match('/<w:b(\/>|\s+[^>]*\/>)/', $rXml);
        $isItalic = preg_match('/<w:i(\/>|\s+[^>]*\/>)/', $rXml);
        $isUnderline = preg_match('/<w:u(\/>|\s+[^>]*\/>)/', $rXml);
        
        preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/s', $rXml, $tMatches);
        $text = implode('', $tMatches[1]);
        
        if ($text !== '') {
            $textEsc = htmlspecialchars_decode($text);
            if ($isBold) $textEsc = "<strong>$textEsc</strong>";
            if ($isItalic) $textEsc = "<em>$textEsc</em>";
            if ($isUnderline) $textEsc = "<u>$textEsc</u>";
            $pContent .= $textEsc;
        }
    }
    
    if (trim($pContent) === '' && empty($imgHtml)) {
        return "<div style='height: 10px;'></div>";
    }
    
    return $imgHtml . (trim($pContent) !== '' ? "<p style='text-align:$align; margin: 4px 0; line-height: 1.6;'>$pContent</p>" : '');
}

function parseDocxTable($tblXml, $images = []) {
    preg_match_all('/<w:tr[^>]*>(.*?)<\/w:tr>/s', $tblXml, $trMatches);
    $rowsHtml = '';
    
    foreach ($trMatches[1] as $trXml) {
        preg_match_all('/<w:tc[^>]*>(.*?)<\/w:tc>/s', $trXml, $tcMatches);
        $cellsHtml = '';
        foreach ($tcMatches[1] as $tcXml) {
            $cellContent = parseDocxContentXml($tcXml, $images);
            $cellsHtml .= "<td style='padding: 6px 10px; vertical-align: top;'>$cellContent</td>";
        }
        $rowsHtml .= "<tr>$cellsHtml</tr>";
    }
    
    return "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>$rowsHtml</table>";
}
