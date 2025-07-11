        function hightlightMaxValue(id) {
             const table = document.getElementById(id);
                const tbody = table.querySelector('tbody');
                const rows = tbody.querySelectorAll('tr');

                // Define the columns to check (0-indexed)
                // For "Quantity" (index 1), "Price" (index 2), "Sales" (index 3)
                const columnsToHighlight = [1, 2, 3, 4, 5, 6, 7]; 

                columnsToHighlight.forEach(colIndex => {
                    let maxValue = -Infinity; 
                    let minValue = +Infinity; 
                    const columnCells = [];
                    const columnCellsMin = [];


                    // Collect all values and cells for the current column
                    rows.forEach(row => {
                        const cell = row.querySelectorAll('td')[colIndex];
                        if (cell) {
                            if(!cell.classList.contains('noMax'))
                            {
                                const value = parseFloat(cell.textContent.replace(" ", "").replace(" ", "").replace(":", "").replace(":", "")); // Convert text to number
                                if (!isNaN(value)) { // Ensure it's a valid number 
                                    columnCells.push({ cell: cell, value: value });
                                    if(!cell.classList.contains('minVal'))
                                    { 
                                        if (value > maxValue) {
                                            maxValue = value;
                                        } 
                                    } 
                                } 
                            }
                        }
                    });

                    // Collect all values and cells for the current column
                    rows.forEach(row => {
                        const cell = row.querySelectorAll('td')[colIndex];
                        if (cell) { 
                            if(!cell.classList.contains('noMax'))
                            {
                                const value = parseFloat(cell.textContent.replace(" ", "").replace(" ", "").replace(":", "").replace(":", "")); // Convert text to number
                                if (!isNaN(value)) { // Ensure it's a valid number 
                                    columnCellsMin.push({ cell: cell, value: value });
                                    if(cell.classList.contains('minVal'))
                                    { 
                                        if (value < minValue && value!=0  ) {
                                            minValue = value;
                                        } 
                                    } 
                                }
                            }
                        }
                    });




                    // Apply the highlight class to cells with the max value
                    columnCells.forEach(item => {
                        if ( item.value === maxValue && maxValue!=0 ) {
                            item.cell.classList.add('highlight-max');
                        } 
                    });

                    columnCellsMin.forEach(item => {
                        if ( item.value === minValue && minValue!=0 ) {
                            item.cell.classList.add('highlight-max');
                        } 
                    });
                    
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            hightlightMaxValue("SocietyTable");
            hightlightMaxValue("TechnologyTable");
            hightlightMaxValue("EconomyTable");
            hightlightMaxValue("MilitaryTable");
            hightlightMaxValue("ScoreTable"); 
        });