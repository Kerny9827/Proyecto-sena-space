// Set new default font family and font color to mimic Bootstrap's default styling
Chart.defaults.global.defaultFontFamily = 'Nunito', '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
Chart.defaults.global.defaultFontColor = '#968585ff';
Chart.plugins.register(ChartDataLabels);

// Pie Chart con porcentajes
var ctx = document.getElementById("myPieChart");
var myPieChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: ["Modulos aprobados", "Modulos reprobados", "Modulos restantes"],
    datasets: [{
      data: [5 ,3 ,2 ],
      backgroundColor: ['#2e8b57', '#c40a0aff', '#1e3c72'],
      hoverBackgroundColor: ['#2e8b5694', '#c40a0aab', '#1e3b7296'],
      hoverBorderColor: "rgba(234, 236, 244, 0)",
    }],
  },
  options: {
    maintainAspectRatio: false,
    tooltips: {
      backgroundColor: "rgb(255,255,255)",
      bodyFontColor: "#858796",
      borderColor: '#dddfeb',
      borderWidth: 1,
      xPadding: 15,
      yPadding: 15,
      displayColors: false,
      caretPadding: 10,

      // Mostrar porcentajes en los tooltips
      callbacks: {
        label: function(tooltipItem, data) {
          var dataset = data.datasets[tooltipItem.datasetIndex];
          var total = dataset.data.reduce(function(previousValue, currentValue) {
            return previousValue + currentValue;
          });
          var currentValue = dataset.data[tooltipItem.index];
          var percentage = Math.round(((currentValue/total) * 100));
          return data.labels[tooltipItem.index] + ': ' + percentage + '%';
        }
      }
    },
    legend: {
      display: false
    },
    cutoutPercentage: 80,
    // Agregar plugin para mostrar porcentajes en el gráfico
    plugins: {
      datalabels: {
        formatter: function(value, context) {
          var sum = context.dataset.data.reduce(function(a, b) {
            return a + b;
          }, 0);
          var percentage = Math.round((value / sum) * 100);
          return percentage + '%';
        },
        color: '#fff',
        font: {
          weight: 'bold',
          size: 12
        }
      }
    }
  },
});

