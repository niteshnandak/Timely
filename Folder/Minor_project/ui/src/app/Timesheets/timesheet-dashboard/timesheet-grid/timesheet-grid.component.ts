import { Component, inject } from '@angular/core';
import { DataServiceRouteService } from '../../../services/data-service-route.service';
import { timesheet } from '../../../models/timesheet';
import { GridModule } from '@progress/kendo-angular-grid';
import { GridData } from '../../../models/griddata';
import { GridDataResult } from '@progress/kendo-angular-grid';
import { CompositeFilterDescriptor, GroupDescriptor, GroupResult, SortDescriptor, filterBy, orderBy, process } from '@progress/kendo-data-query';
import { groupBy } from '@progress/kendo-data-query';
import { and } from '@progress/kendo-angular-grid/utils';
import { filter } from '@progress/kendo-data-query/dist/npm/transducers';
import { FormGroup, ReactiveFormsModule, FormBuilder, Validators } from '@angular/forms';
import { NgClass } from '@angular/common';
import { ViewChild, NgZone, AfterViewInit } from "@angular/core";
import { take } from 'rxjs';
import { ActivatedRoute, NavigationExtras, Router } from '@angular/router';

@Component({
  selector: 'app-timesheet-grid',
  standalone: true,
  imports: [
    GridModule, ReactiveFormsModule, NgClass
  ],
  templateUrl: './timesheet-grid.component.html',
  styleUrl: './timesheet-grid.component.css'
})
export class TimesheetGridComponent {
  showManualCreation = false;
  showUploadCSV = false;
  groups:GroupDescriptor[] = [];
  timesheets:any[] = []
  gridData:any = {data:[], total: 0};
  user:string = "";
  gridloading = false;
  router = inject(Router);


  grid: any|GridDataResult = {
    data: [],
    total: 0
  };

  filter: CompositeFilterDescriptor = {
    logic: "and",
    filters: [],
  }

  dataService = inject(DataServiceRouteService)
  pageSize = 10;
  skip = 0;

  total = 0;
  ngZone: any;

  ngOnInit(){
    this.skip = window.history.state.skip;

    this.loadItem();
  }

  pageChange(event: any){
    this.skip = event.skip;
    this.pageSize = event.take;
    this.loadItem();
  }

  groupChange(groups: any){
    this.groups = groups;
    this.groupItem();
  }

  filterChange(filter: CompositeFilterDescriptor){
    this.filter={
      logic:filter.logic,
      filters:filter.filters
    }
    this.filterItem();
  }

  filterItem(){
    console.log(filter)
    this.grid = {
      data: filterBy(this.timesheets, this.filter),
      total: this.total
    }
    console.log(this.grid)
  }

  groupItem(){
    if(this.groups.length == 0){
      this.loadItem();
    }
    this.grid = groupBy(this.timesheets, this.groups);
  }

  loadItem(){
    this.gridloading = true;
    this.dataService.fetchDataTimesheet(this.skip, this.pageSize).subscribe((response)=>{
      this.timesheets = response.timesheets;
      this.user = response.user;
      this.total = response.total;
      console.log(this.timesheets);

      this.grid = {
        data: this.timesheets,
        total: this.total
      }
      console.log(this.grid)
      this.gridloading = false;
    })

  }

  navigateToTimehsheetDetail(id:number){
    const navigationExtras:NavigationExtras = { state: {skip: this.skip} };
    console.log(navigationExtras);
    this.router.navigateByUrl('timesheet/'+id, navigationExtras);
  }

}

