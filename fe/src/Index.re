[%bs.raw {|require('./Index.scss')|}];
ReactDOMRe.renderToElementWithId(
  <ReasonApollo.Provider client=Client.instance>
    <AppContainer />
  </ReasonApollo.Provider>,
  "mountPoint",
);