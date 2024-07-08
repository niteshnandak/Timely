import { Component, ElementRef, AfterViewInit, ViewChild, Input, OnChanges } from '@angular/core';
import { Chart, ChartType, ChartConfiguration, registerables } from 'chart.js';
Chart.register(...registerables);

@Component({
  selector: 'app-line-chart',
  standalone: true,
  templateUrl: './line-chart.component.html',
})
export class LineChartComponent implements AfterViewInit {
  
  @ViewChild('lineChart') private chartRef!: ElementRef<HTMLCanvasElement>;
  private chart!: Chart;
  
  @Input() lineChartData : any;
  
  ngAfterViewInit(): void {
    //initiate chart after view init
    this.initChart();
  }
  
  ngOnChanges() { 
    if (this.lineChartData) {
      // when new data recieved, update chart
      this.updateChartData(this.lineChartData); 
    }
  }

  public labels = [
    'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
    'Jul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'
  ];

  public data = {
    labels: this.labels,
    datasets: [{
      label: 'Assignments',
      data:[] as number[],
      fill: true,
      backgroundColor: 'rgba(75, 192, 192, 0.2)',
      borderColor: 'rgb(75, 192, 192)',
      borderWidth: 2,
      pointBackgroundColor: 'rgb(75, 192, 192)',
      pointBorderColor: '#fff',
      pointBorderWidth: 2,
      pointRadius: 5,
      tension: 0.2
    }]
  };

  public chartOptions: ChartConfiguration<'line'>['options'] = {
    maintainAspectRatio: false,
    responsive: true,
    scales: {
      x: {
        ticks: {
          font: {
            size: 10,
            family: 'Libre Franklin',
            weight: 'normal',
          }
        },
        display: true,
        title: {
          display: true,
          text: 'Month'
        }
      },
      y: {
        beginAtZero: true,
        ticks: {
          font: {
            size: 10,
            family: 'Libre Franklin',
            weight: 'normal',
          }
        },
        display: true,
        title: {
          display: true,
          text: 'Value'
        }
      }
    },
    plugins: {
      legend: {
        labels: {
          font: {
            size: 12,
            family: 'Libre Franklin',
          },
        },
      }
    },
  };



  private initChart(): void {
    const canvas = this.chartRef.nativeElement;
    const ctx = canvas.getContext('2d');

    if (ctx) {
      this.chart = new Chart(ctx, {
        type: 'line',
        data: this.data,
        options: this.chartOptions
      });
    }
  }

  private updateChartData(newData: any[]): void {
    if(this.chart){
      this.data.datasets[0].data = newData;
      this.chart.update();
  } else {
    console.warn('Chart is not yet created');
  }}
}
