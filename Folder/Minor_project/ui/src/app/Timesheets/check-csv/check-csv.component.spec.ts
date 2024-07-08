import { ComponentFixture, TestBed } from '@angular/core/testing';

import { CheckCsvComponent } from './check-csv.component';

describe('CheckCsvComponent', () => {
  let component: CheckCsvComponent;
  let fixture: ComponentFixture<CheckCsvComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [CheckCsvComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(CheckCsvComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
