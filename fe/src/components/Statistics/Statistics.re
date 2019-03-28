let component = ReasonReact.statelessComponent(__FILE__);

let make = _children => {
  ...component,
  render: _ => <StatisticsChartContainer />,
};
