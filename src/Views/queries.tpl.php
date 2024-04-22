{! hlstyle !}
<table>
    <thead>
        <tr>
            <th class="debug-bar-width6r">Time</th>
            <th>Query String</th>
        </tr>
    </thead>
    <tbody>
    {queries}
        <tr class="{class}" title="{hover}" data-toggle="{qid}-trace">
            <td class="narrow" style="vertical-align: top;">
                <small>
                    {duration}<br>
                    <u>Record(s):</u><br>
                    {numRows}
                </small>
            </td>
            <td><u>{trace-file}</u>{! sql !}</td>
        </tr>
        <tr class="muted debug-bar-ndisplay" id="{qid}-trace">
            <td></td>
            <td>
            {trace}
                {index}<strong>{file}</strong><br/>
                {function}<br/><br/>
            {/trace}
            </td>
        </tr>
    {/queries}
    </tbody>
</table>