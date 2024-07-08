import { ComponentFixture, TestBed } from '@angular/core/testing';

import { PeopleAssignmentComponent } from './people-assignment.component';

describe('PeopleAssignmentComponent', () => {
  let component: PeopleAssignmentComponent;
  let fixture: ComponentFixture<PeopleAssignmentComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [PeopleAssignmentComponent]
    })
    .compileComponents();
    
    fixture = TestBed.createComponent(PeopleAssignmentComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
