import { Component, OnInit, ViewChild, ElementRef, AfterViewInit, Input } from '@angular/core';
import { Chart, registerables, ChartConfiguration } from 'chart.js';

Chart.register(...registerables);

@Component({
  standalone: true,
  selector: 'app-stack-bar-chart',
  templateUrl: './stack-bar-chart.component.html'
})
export class StackBarChartComponent implements AfterViewInit {
  @ViewChild('stackBarChart', { static: true }) private chartRef!: ElementRef<HTMLCanvasElement>;
  private chart!: Chart<'bar', number[], string>;
  
  @Input() stackChartData : any;
  
  constructor() { }  
  
  ngAfterViewInit(): void {
    this.initChart();
  }

  ngOnChanges(){
    if(this.stackChartData){
      this.updateChartData(this.stackChartData);
    }
  }

  public data = {
    labels: [],
    datasets: []    
  };
  
  public chartOptions: ChartConfiguration<'bar'>['options'] = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y',
    scales: {
      x: {
        stacked: true,
        ticks: {
          font: {
            size: 12,
            family: 'Libre Franklin',
          }
        },
        title: {
          display: true,
          text: 'No. of Assignments',
          font: {
            size: 10,
            family: 'Libre Franklin',
          }
        }
      },
      y: {
        stacked: true,
        ticks: {
          font: {
            size: 12,
            family: 'Libre Franklin',
          }
        },
        title: {
          display: true,
          text: 'Companies',
          font: {
            size: 10,
            family: 'Libre Franklin',
          }
        }
      }
    },
    plugins: {
      legend: {
        position: 'right',
        labels: {
          font: {
            size: 12,
            family: 'Libre Franklin',
          }
        }
      },
      tooltip: {
        titleFont: {
          size: 12,
          family: 'Libre Franklin',
          weight: 'normal'
        },
        bodyFont: {
          size: 12,
          family: 'Libre Franklin',
          weight: 'normal'
        },
        backgroundColor: 'rgba(0,0,0,0.5)', 
        borderWidth: 1,
        boxPadding: 10, 
        caretPadding: 5,
        cornerRadius: 4, 
        multiKeyBackground: 'rgba(0,0,0,0.1)'
      }
    }
  };
  


  private initChart(): void {
    const ctx = this.chartRef.nativeElement.getContext('2d');
    if (ctx) {
      this.chart = new Chart(ctx, {
        type: 'bar',
        data: this.data,
        options: this.chartOptions,
      });
    }
  }

  private updateChartData(data: any){
    this.data.labels = data.labels;
    this.data.datasets = data.datasets;

    this.chart.update();
  }
}
