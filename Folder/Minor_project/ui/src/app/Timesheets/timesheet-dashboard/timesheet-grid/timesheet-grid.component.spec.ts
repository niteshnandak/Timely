import { ComponentFixture, TestBed } from '@angular/core/testing';

import { TimesheetGridComponent } from './timesheet-grid.component';

describe('TimesheetGridComponent', () => {
  let component: TimesheetGridComponent;
  let fixture: ComponentFixture<TimesheetGridComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [TimesheetGridComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(TimesheetGridComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
