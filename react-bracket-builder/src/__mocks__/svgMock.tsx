import * as React from 'react'

const SvgMock: React.FunctionComponent<
  React.SVGProps<SVGSVGElement> & { title?: string }
> = ({ title, ...restProps }) => (
  <svg {...restProps}>{title && <title>{title}</title>}</svg>
)

export const ReactComponent = SvgMock
const src: string = '' // You can provide a default or leave it empty
export default src
