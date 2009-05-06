<?xml version="1.0" encoding="UTF-8"?>
<document orientation="P" unit="mm" format="A4">
    <!--{strip}-->
    <!-- Main configuration -->
    <!--{assign var="borderLeft" value=20}-->
    <!--{assign var="borderRight" value=20}-->
    <!--{assign var="borderTop" value=20}-->
    <!--{assign var="borderBottom" value=20}-->
    <!--{assign var="paperWidth" value=210}-->
    <!--{assign var="paperHeight" value=297}-->
    <!--{/strip}-->
    
    <header>
        <allpages>
            <!-- ########################### GENERATE HEADING ########################################### -->
            <setfont family="Arial" style="" size="14" />
            <sety y="<!--{$borderTop}-->" />
            <setx x="<!--{$borderLeft}-->" />
            <cell x="0" w="<!--{$paperWidth-$borderLeft-$borderRight}-->" align="C">Some Header</cell> 
        </allpages>
    </header>


    <footer>
        <allpages>
            <sety y="<!--{$paperHeight-$borderBottom}-->" />
            <pagenocell x="<!--{$paperWidth-$borderLeft-165}-->" w="165" align="R" label="Seite " />
        </allpages>
    </footer>


    <content>
        <addpage />
        <!-- ########################### GENERATE TABLE CONTENTS ##################################### -->
        
        <setfont family="Arial" style="" size="12" />
        
        <!--{foreach from=$listItems item=row name="rows"}-->
            <!--{foreach name="listItems" from=$row item=value key=columnDescriptionIdentifier}-->
                <!--{assign var="col" value=$smarty.foreach.listItems.iteration-1}-->
                <setx x="<!--{$__config.column_positions_scaled[$col]}-->" />
                <cell border="1" w="<!--{$__config.column_widths_scaled[$col]}-->" align="<!--{$__config.column_alignments[$col]}-->"><!--{$value}--></cell>
            <!--{/foreach}-->
            <sety y="+5" />
        <!--{/foreach}-->
        <addpage iflessthan="<!--{$borderBottom}-->" />
        <!--<line x1="<!--{$borderLeft}-->" x2="<!--{$paperWidth-$borderRight}-->" />--> 

    </content>
</document>
