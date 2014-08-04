<?php
class mtmdView {

    protected $contents = array();

    public function __construct()
    {

    }


    public function render($head, $content)
    {
        $tpl = file_get_contents('lib/mtmd/templates/base.html.tpl');
        return sprintf(
            $tpl,
            $head,
            $content
        );
    }


    public function renderTable($columns, array $contents)
    {
        $tplTable = '<div class="table">%s</div>'."\n";
        $tplRow   = '<div class="row">%s</div>'."\n";
        $tplCol   = '<div class="column">%s</div>'."\n";

        $rows = '';
        $colArr = array();
        $tmpArr = array();
        $rowCount = 0;
        $colCount = 0;
        $total = count($contents);

        foreach ($contents as $k => $content) {
            $colCount++;
            array_push($tmpArr, $content);
            if ($colCount === $columns || $k == $total-1) {
                if ($k == $total-1) {
                    for ($j = 0; $j < $columns-$colCount; $j++) {
                        array_push($tmpArr, '&nbsp;');
                    }
                }
                $colArr[$rowCount] = $tmpArr;
                $tmpArr = array();
                $colCount = 0;
                $rowCount++;
                continue;
            }
        }

        foreach ($colArr as $row) {
            $cols = '';
            foreach($row as $col) {
                $cols .= sprintf(
                    $tplCol,
                    $col."\n"
                );
            }

            $rows .= sprintf(
                $tplRow,
                $cols
            );
        }


        $table = sprintf(
            $tplTable,
            $rows
        );

        return $table;

    }


}