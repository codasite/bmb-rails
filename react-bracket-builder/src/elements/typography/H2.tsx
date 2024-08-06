export const H2 = (props: {
  children: React.ReactNode
  className?: string
}) => {
  const defaultClasses = 'tw-text-24 sm:tw-text-32 md:tw-text-48 lg:tw-text-64'
  const classes = props.className
    ? `${defaultClasses} ${props.className}`
    : defaultClasses
  return <h2 className={classes}>{props.children}</h2>
}
