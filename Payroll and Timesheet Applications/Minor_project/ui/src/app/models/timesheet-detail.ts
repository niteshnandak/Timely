export interface timesheetDetail{
    id?: number
    check_empty:boolean,
    csv_flag:boolean,
    timesheet_detail:any[],
    timesheet_name:string,
    total:number,
    user:string
}