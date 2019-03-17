let component = ReasonReact.statelessComponent("Statistics");

let make = _children => {
  ...component,
  render: _ => <StatisticsChartContainer />,
};
