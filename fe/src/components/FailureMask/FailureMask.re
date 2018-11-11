[%bs.raw {|require('./FailureMask.scss')|}];
open ReasonReact;

let component = statelessComponent("FailureMask");

let imgNosacz = <img src="fe/src/images/nosacz.jpg" />;

let make = _children => {
  ...component,
  render: _self =>
    <div className="FailureMask">
      imgNosacz
      <div className="label">
        {"Kuuuuuuurla, cos sie zrombalo !" |> ReasonReact.string}
      </div>
    </div>,
};