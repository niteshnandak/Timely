import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CheckReportsComponent } from './check-reports.component';

describe('CheckReportsComponent', () => {
  let component: CheckReportsComponent;
  let fixture: ComponentFixture<CheckReportsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CheckReportsComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(CheckReportsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
