{! hlstyle !}
<table>
    <thead>
        <tr>
            <th class="debug-bar-width6r">Time</th>
            <th class="debug-bar-width4r"><small>Row</small></th>
            <th>Query String</th>
        </tr>
    </thead>
    <tbody>
    {queries}
        <tr>
            <td class="narrow">{duration}</td>
            <td class="narrow">{numRows}</td>
            <td class="narrow">{! sql !}</td>
        </tr>
    {/queries}
    </tbody>
</table>
