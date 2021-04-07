
let visitsOfLectures = document.getElementById("visitsOfLectures").value.split(",");
let lecturesName = [];
for (let i = 1; i <= visitsOfLectures.length; i++) {
    lecturesName.push("prednáška " + i +".");
}

console.log(lecturesName);
console.log(visitsOfLectures);

var ctx = document.getElementById('myChart').getContext('2d');
var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: lecturesName,
        datasets: [{
            label: 'študenti',
            data: visitsOfLectures,
            backgroundColor: [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(255, 99, 132, 0.2)',
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(255, 99, 132, 1)',
            ],
            borderWidth: 1
        }]
    },
    options: {
        plugins: {
            title: {
                display: true,
                text: 'Účasť na prednáškach',
                font: {
                    family: "Saira",
                    size: 60,
                    style: 'normal',
                    color: 'black',

                }
            },
            legend: {
                display: false,
                labels: {
                    // This more specific font property overrides the global property
                    font: {
                        size: 20,
                        family: "Saira",
                        color: 'black',
                    }
                }
            }

        },
        scale: {
            pointLabels :{
                fontStyle: "bold",
            }
        },
        responsive: true,
        scales: {
            y: {
                title: {
                    display: true,
                    text: "Počet študentov",
                    font: {
                        family: "Saira",
                        size: 60,
                        style: 'normal',
                        color: 'black',

                    }
                },
                beginAtZero: true,
            },
        }
    }
});

 $(document).ready( function () {
    $('#lectures').DataTable({searching: false, paging: false, info: false, "order": [[ 1, "asc" ]]
    });
} );